<?php
declare(strict_types=1);
/**
 * TokenController.php
 * Copyright (c) 2020 james@firefly-iii.org
 *
 * This file is part of the Firefly III CSV importer
 * (https://github.com/firefly-iii/csv-importer).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use GrumpyDictator\FFIIIApiSupport\Exceptions\ApiHttpException;
use GrumpyDictator\FFIIIApiSupport\Request\SystemInformationRequest;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use InvalidArgumentException;
use JsonException;
use Log;
use Str;

/**
 * Class TokenController
 */
class TokenController extends Controller
{
    /**
     * Start page where the user will always end up on. Will do either of 3 things:
     *
     * 1. All info is present. Set some cookies and continue.
     * 2. Has client ID + URL. Will send user to Firefly III for permission.
     * 3. Has either 1 of those. Will show user some input form.
     * @param Request $request
     *
     * @return Application|Factory|RedirectResponse|Redirector|View
     */
    public function index(Request $request)
    {
        $pageTitle = 'CSV importer';
        Log::debug(sprintf('Now at %s', __METHOD__));
        $configToken = (string) config('csv_importer.access_token');
        $clientId    = (int) config('csv_importer.client_id');
        $baseURL     = (string) config('csv_importer.url');
        $vanityURL   = $baseURL;
        if ('' !== (string) config('csv_importer.vanity_url')) {
            $vanityURL = config('csv_importer.vanity_url');
        }

        Log::info('The following configuration information was found:');
        Log::info(sprintf('Personal Access Token: "%s" (limited to 25 chars if present)', substr($configToken, 0, 25)));
        Log::info(sprintf('Client ID            : "%s"', $clientId));
        Log::info(sprintf('Base URL             : "%s"', $baseURL));
        Log::info(sprintf('Vanity URL           : "%s"', $vanityURL));

        // Option 1: access token and url are present:
        if ('' !== $configToken && '' !== $baseURL) {
            Log::debug(sprintf('Found personal access token + URL "%s" in config, set cookie and return to index.', $baseURL));

            // set cookies.
            $cookies = [
                cookie('access_token', $configToken),
                cookie('base_url', $baseURL),
                cookie('vanity_url', $vanityURL),
                cookie('refresh_token', ''),
            ];

            return redirect(route('index'))->withCookies($cookies);
        }

        // Option 2: client ID + base URL.
        if (0 !== $clientId && '' !== $baseURL) {
            Log::debug(sprintf('Found client ID "%d" + URL "%s" in config, redirect to Firefly III for permission.', $clientId, $baseURL));
            return $this->redirectForPermission($request, $baseURL, $vanityURL, $clientId);
        }

        // Option 3: either is empty, ask for client ID and/or base URL:
        $clientId = 0 === $clientId ? '' : $clientId;

        return view('token.client_id', compact('baseURL', 'clientId', 'pageTitle'));
    }

    /**
     * This method forwards the user to Firefly III. Some parameters are stored in the user's session.
     *
     * @param Request $request
     * @param string  $baseURL
     * @param string  $vanityURL
     * @param int     $clientId
     *
     * @return RedirectResponse
     */
    private function redirectForPermission(Request $request, string $baseURL, string $vanityURL, int $clientId): RedirectResponse
    {
        $baseURL   = rtrim($baseURL, '/');
        $vanityURL = rtrim($vanityURL, '/');


        Log::debug(sprintf('Now in %s(request, "%s", "%s", %d)', __METHOD__, $baseURL, $vanityURL, $clientId));
        $state        = Str::random(40);
        $codeVerifier = Str::random(128);
        $request->session()->put('state', $state);
        $request->session()->put('code_verifier', $codeVerifier);
        $request->session()->put('form_client_id', $clientId);
        $request->session()->put('form_base_url', $baseURL);
        $request->session()->put('form_vanity_url', $vanityURL);

        $codeChallenge = strtr(rtrim(base64_encode(hash('sha256', $codeVerifier, true)), '='), '+/', '-_');
        $params        = [
            'client_id'             => $clientId,
            'redirect_uri'          => route('token.callback'),
            'response_type'         => 'code',
            'scope'                 => '',
            'state'                 => $state,
            'code_challenge'        => $codeChallenge,
            'code_challenge_method' => 'S256',
        ];
        $query         = http_build_query($params);
        // we redirect the user to the vanity URL, which is the same as the base_url, unless the user actually set a vanity URL.
        $finalURL = sprintf('%s/oauth/authorize?', $vanityURL);
        Log::debug('Query parameters are', $params);
        Log::debug(sprintf('Now redirecting to "%s" (params omitted)', $finalURL));

        return redirect($finalURL . $query);
    }

    /**
     * User submits the client ID + optionally the base URL.
     * Whatever happens, we redirect the user to Firefly III and beg for permission.
     *
     * @param Request $request
     * @return Application|RedirectResponse|Redirector
     */
    public function submitClientId(Request $request)
    {
        Log::debug(sprintf('Now at %s', __METHOD__));
        $data = $request->validate(
            [
                'client_id' => 'required|numeric|min:1|max:65536',
                'base_url'  => 'url',
            ]
        );
        Log::debug('Submitted data: ', $data);

        if (true === config('csv_importer.expect_secure_url') && 'https://' !== substr($data['base_url'], 0, 8)) {
            $request->session()->flash('secure_url', 'URL must start with https://');

            return redirect(route('token.index'));
        }

        $data['client_id'] = (int) $data['client_id'];

        // grab base URL from config first, otherwise from submitted data:
        $baseURL = config('csv_importer.url');
        Log::debug(sprintf('Base URL is "%s"', $baseURL));
        $vanityURL = $baseURL;

        Log::debug(sprintf('Vanity URL is now "%s"', $vanityURL));

        // if the config has a vanity URL it will always overrule.
        if ('' !== (string) config('csv_importer.vanity_url')) {
            $vanityURL = config('csv_importer.vanity_url');
            Log::debug(sprintf('Vanity URL is now "%s"', $vanityURL));
        }

        // otherwise take base URL from the submitted data:
        if (array_key_exists('base_url', $data) && '' !== $data['base_url']) {
            $baseURL = $data['base_url'];
            Log::debug(sprintf('Base URL is now "%s"', $baseURL));
        }
        if ('' === (string) $vanityURL) {
            $vanityURL = $baseURL;
            Log::debug(sprintf('Vanity URL is now "%s"', $vanityURL));
        }

        // return request for permission:
        return $this->redirectForPermission($request, $baseURL, $vanityURL, $data['client_id']);
    }

    /**
     * This method will check if Firefly III accepts the access_token from the cookie
     * and the base URL (also from the cookie). The base_url is NEVER the vanity URL.§
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function doValidate(Request $request): JsonResponse
    {
        Log::debug(sprintf('Now at %s', __METHOD__));
        $response = ['result' => 'OK', 'message' => null];
        $url      = (string) $request->cookie('base_url');
        $token    = (string) $request->cookie('access_token');
        $request  = new SystemInformationRequest($url, $token);

        $request->setVerify(config('csv_importer.connection.verify'));
        $request->setTimeOut(config('csv_importer.connection.timeout'));

        try {
            $result = $request->get();
        } catch (ApiHttpException $e) {
            return response()->json(['result' => 'NOK', 'message' => $e->getMessage()]);
        }
        // -1 = OK (minimum is smaller)
        // 0 = OK (same version)
        // 1 = NOK (too low a version)

        $minimum = (string) config('csv_importer.minimum_version');
        $compare = version_compare($minimum, $result->version);
        if (1 === $compare) {
            $errorMessage = sprintf(
                'Your Firefly III version %s is below the minimum required version %s',
                $result->version, $minimum
            );
            $response     = ['result' => 'NOK', 'message' => $errorMessage];
        }

        return response()->json($response);
    }

    /**
     * The user ends up here when they come back from Firefly III.
     *
     * @param Request $request
     */
    public function callback(Request $request)
    {
        Log::debug(sprintf('Now at %s', __METHOD__));
        $state        = (string) $request->session()->pull('state');
        $codeVerifier = (string) $request->session()->pull('code_verifier');
        $clientId     = (int) $request->session()->pull('form_client_id');
        $baseURL      = (string) $request->session()->pull('form_base_url');
        $vanityURL    = (string) $request->session()->pull('form_vanity_url');
        $code         = $request->get('code');

        throw_unless(
            strlen($state) > 0 && $state === $request->state,
            InvalidArgumentException::class
        );
        // always POST to the base URL, never the vanity URL.
        $finalURL = sprintf('%s/oauth/token', $baseURL);
        $params   = [
            'form_params' => [
                'grant_type'    => 'authorization_code',
                'client_id'     => $clientId,
                'redirect_uri'  => route('token.callback'),
                'code_verifier' => $codeVerifier,
                'code'          => $code,
            ],
        ];
        Log::debug('State is valid!');
        Log::debug('Params for access token', $params);
        Log::debug(sprintf('Will contact "%s" for a token.', $finalURL));

        $opts = [
            'verify'          => config('csv_importer.connection.verify'),
            'connect_timeout' => config('csv_importer.connection.timeout'),
        ];
        try {
            $response = (new Client($opts))->post($finalURL, $params);
        } catch (ClientException $e) {
            $body = (string) $e->getResponse()->getBody();
            Log::error(sprintf('Client exception when decoding response: %s', $e->getMessage()));
            Log::error(sprintf('Response from server: "%s"', $body));
            Log::error($e->getTraceAsString());
            return view('error')->with('message', $e->getMessage())->with('body', $body);
        }

        try {
            $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            Log::error(sprintf('JSON exception when decoding response: %s', $e->getMessage()));
            Log::error(sprintf('Response from server: "%s"', (string) $response->getBody()));
            Log::error($e->getTraceAsString());
            throw new ApiException(sprintf('JSON exception when decoding response: %s', $e->getMessage()));
        }
        Log::debug('Response', $data);

        // set cookies.
        $cookies = [
            cookie('access_token', (string) $data['access_token']),
            cookie('base_url', $baseURL),
            cookie('vanity_url', $vanityURL),
            cookie('refresh_token', (string) $data['refresh_token']),
        ];
        Log::debug(sprintf('Return redirect with cookies to "%s"', route('index')));

        return redirect(route('index'))->withCookies($cookies);
    }
}
