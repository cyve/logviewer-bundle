# UserBundle

## Installation:

With [Composer](http://packagist.org):
```sh
composer require cyve/logviewer-bundle
```

## Configuration

```php
// config/bundles.php
return [
    ...
    Cyve\LogViewerBundle\CyveLogViewerBundle::class => ['all' => true],
];
```
```yaml
// config/routes/cyve_log_viewer.yaml
cyve_log_viewer:
    resource: "@CyveLogViewerBundle/Resources/config/routing.yaml"
```

## Usage

The log viewer is available for users granted with `ROLE_SUPER_ADMIN` at `http://127.0.0.1/logs`
