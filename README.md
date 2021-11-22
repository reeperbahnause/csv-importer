[![Packagist][packagist-shield]][packagist-uri]
[![License][license-shield]][license-uri]
[![Stargazers][stars-shield]][stars-url]
[![Donate][donate-shield]][donate-uri]

<!-- PROJECT LOGO -->
<br />
<p align="center">
      <img src="https://fireflyiiiwebsite.z6.web.core.windows.net/assets/logo/small.png" alt="Firefly III" width="120" height="178">
  </a>
</p>
  <h1 align="center">Firefly III CSV importer</h1>

  <p align="center">
    A tool to import CSV files into Firefly III
    <br />
    <a href="https://docs.firefly-iii.org/csv"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="https://demo.firefly-iii.org/">View Firefly III demo</a>
    ·
    <a href="https://github.com/firefly-iii/firefly-iii/issues">Report Bug</a>
    ·
    <a href="https://github.com/firefly-iii/firefly-iii/issues">Request Feature</a>
  </p>

<!-- MarkdownTOC autolink="true" -->

- [About the Firefly III CSV importer](#about-the-firefly-iii-csv-importer)
  - [Purpose](#purpose)
  - [Features](#features)
  - [Who's it for?](#whos-it-for)
- [Getting Started](#getting-started)
  - [Using the web interface](#using-the-web-interface)
  - [Using the command line](#using-the-command-line)
  - [Using the auto-import](#using-the-auto-import)
- [Known import challenges](#known-import-challenges)
- [Other stuff](#other-stuff)
  - [Contribute](#contribute)
  - [Donate](#donate)
  - [Contact](#contact)

<!-- /MarkdownTOC -->

## About the Firefly III CSV importer

Welcome! You found the CSV importer, a tool to import CSV files into [Firefly III](https://github.com/firefly-iii/firefly-iii). The name kind of gave it away, didn't it?

This tool is built and designed to make it easy to import data into your Firefly III installation. It uses CSV files (duh) and optionally, a config file ([from your bank perhaps?](https://github.com/firefly-iii/import-configurations/)) to make everything go smooth.

### Purpose

Use this tool to (automatically) import your bank's CSV files into Firefly III. If you're a bit of a developer, feel free to use this code to generate your own import tool.

### Features
	
* This tool will let you download or generate a configuration file, so the next import will go faster.
* It will not import duplicate transactions.
* It can recognize all currencies, transaction types and other things that Firefly III supports.

### Who's it for?

Anybody who uses Firefly III and wants to automatically import files.

## Getting Started

You can use this tool in several ways.

1. [Install it on your server using composer](https://docs.firefly-iii.org/csv/install/self_hosted/).
2. [Use the Docker-image](https://docs.firefly-iii.org/csv/install/docker/).
3. [Use the public instance](https://docs.firefly-iii.org/csv/help/public/).

Generally speaking, it's easiest to use and install this tool the same way as you use Firefly III. And although it features an excellent web-interface, you can also use the command line to import your data.

There are [upgrade instructions](https://docs.firefly-iii.org/csv/upgrade/upgrade/) for both methods of installation.

The [full usage instructions](https://docs.firefly-iii.org/csv) can be found in the documentation. Basically, this is the workflow:

### Using the web interface

1. [Set up and configure the CSV importer](https://docs.firefly-iii.org/csv/install/configure/).
2. [Upload your CSV file](https://docs.firefly-iii.org/csv/usage/upload/).
3. [Tell the importer what your CSV file looks like.](https://docs.firefly-iii.org/csv/usage/configure/).
4. [Set each column's role and data type](https://docs.firefly-iii.org/csv/usage/roles/).
5. [Map values in the CSV file to existing values in your database](https://docs.firefly-iii.org/csv/usage/map/).
6. [Enjoy the result in Firefly III](https://github.com/firefly-iii/firefly-iii).

### Using the command line

1. [Set up and configure the CSV importer](https://docs.firefly-iii.org/csv/install/configure/).
2. [Follow the command line instructions](https://docs.firefly-iii.org/csv/usage/command_line/)

### Using the auto-import

1. Import at least once, so you'll have a configuration file.
2. Or, get a configuration file from [the repository](https://github.com/firefly-iii/import-configurations).
3. [Run the Docker inline import command](https://docs.firefly-iii.org/csv/usage/command_line/)

## Known import challenges

Most people run into the same problems when importing data into Firefly III. Read more about those on the following pages:

1. [Issues with your tokens](https://docs.firefly-iii.org/csv/errors/token_errors/)
1. [Often seen errors and issues](https://docs.firefly-iii.org/csv/errors/freq_errors/).
2. [Frequently asked questions](https://docs.firefly-iii.org/csv/errors/freq_questions/).
3. [My bank delivers bad CSV files, what do I do now?](https://docs.firefly-iii.org/csv/errors/bad_files/)

## Other stuff

### Contribute

There are some [contributing guidelines](https://github.com/firefly-iii/csv-importer/blob/master/.github/contributing.md) and a [code of conduct](https://github.com/firefly-iii/csv-importer/blob/master/.github/code_of_conduct.md), which I invite you to check out.

For all other contributions, see below.

<!-- SPONSOR TEXT -->
### Donate

If you feel Firefly III made your life better, consider contributing as a sponsor. Please check out my [Patreon](https://www.patreon.com/jc5) and [GitHub Sponsors](https://github.com/sponsors/JC5) page for more information. Thank you for considering.


<!-- END OF SPONSOR -->

This work [is licensed](https://github.com/firefly-iii/csv-importer/blob/master/LICENSE) under the [GNU Affero General Public License v3](https://www.gnu.org/licenses/agpl-3.0.html).

### Contact

You can contact me at [james@firefly-iii.org](mailto:james@firefly-iii.org), you may open an issue in the [main repository](https://github.com/firefly-iii/firefly-iii) or contact me through [gitter](https://gitter.im/firefly-iii/firefly-iii) and [Twitter](https://twitter.com/Firefly_III).

[![Requires PHP8.0][php-shield]][php-uri]

[php-shield]: https://img.shields.io/badge/php-8.0-red.svg?style=flat-square
[php-uri]: https://secure.php.net/downloads.php
[packagist-shield]: https://img.shields.io/packagist/v/firefly-iii/csv-importer.svg?style=flat-square
[packagist-uri]: https://packagist.org/packages/firefly-iii/csv-importer
[license-shield]: https://img.shields.io/github/license/firefly-iii/csv-importer.svg?style=flat-square
[license-uri]: https://www.gnu.org/licenses/agpl-3.0.html
[stars-shield]: https://img.shields.io/github/stars/firefly-iii/csv-importer.svg?style=flat-square
[stars-url]: https://github.com/firefly-iii/csv-importer/stargazers
[donate-shield]: https://img.shields.io/badge/donate-%24%20%E2%82%AC-brightgreen?style=flat-square
[donate-uri]: #support
