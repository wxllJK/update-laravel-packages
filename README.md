# Update Laravel Packages

This is a Composer package that allows you to automatically update the packages detailed in the Laravel upgrade guides in your composer.json.

## Installation

You can install the package via Composer by adding it as a dependency to your project's `composer.json` file:

```bash
composer require wxlljk/update-laravel-packages
```

## Usage

To update the Laravel framework version, run the following command:

```bash
./vendor/bin/update-laravel-packages update <new_major_version>
```

Replace `<new_major_version>` with the desired major version you want to update to (e.g., `10`, `11`, etc.).

## Features

- Automatically updates the Laravel framework version in `composer.json` along with any other packages detailed in the upgrade guides.
- Goes through each upgrade guide so no updates are missed
- Simple command-line interface for easy usage.

## Contributing

Contributions are welcome! Feel free to open an issue or submit a pull request if you have any suggestions, bug fixes, or feature enhancements.

## License

This package is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
