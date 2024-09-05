
# Tadasei/backend-trashable-notifications

This package provides stubs for managing trashable (soft deletable) database notifications in the backend of a Laravel application. It simplifies common Index, Store, Update, and Delete notification operations by providing pre-defined structures.

## Features

- Quickly generate trashable database notifications management files and handling logic.
- Customize and extend generated code to fit your project's needs.
- Improve development efficiency by eliminating repetitive tasks.

## Installation

Install the package via Composer by running:

```bash
composer require tadasei/backend-trashable-notifications --dev
```

## Usage

### Publishing Trashable Notifications Management Utilities

To publish the utilities, run:

```bash
php artisan trashable-notifications:install
```

### Configuration

After publishing the utilities, follow these steps to complete the configuration:

1. **Form Request Configuration**: 
   Modify the generated form request (`App\Http\Requests\SendNotificationRequest`) to suit your application's validation rules and logic.

2. **Policy Configuration**: 
   Update the generated policy (`App\Policies\DatabaseNotificationPolicy`) to control access to notification management operations, ensuring it aligns with your project's authorization system.

These steps are necessary to ensure that the package integrates smoothly with your application's existing structure.

### Further Customization

The generated code serves as a starting point. You can further extend and customize it to fit your project’s needs.

## Contributing

Contributions are welcome! If you have suggestions, bug reports, or feature requests, please open an issue on the GitHub repository.

## License

This package is open-source software licensed under the [MIT license](LICENSE).
