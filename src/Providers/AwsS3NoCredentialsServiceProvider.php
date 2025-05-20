<?php

namespace Motor\Core\Providers;

use League\Flysystem\AwsS3V3\PortableVisibilityConverter as AwsS3PortableVisibilityConverter;
use League\Flysystem\Visibility;
use Aws\S3\S3Client;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\AwsS3V3Adapter as LaravelAwsS3V3Adapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;

class AwsS3NoCredentialsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Storage::extend('s3_no_credentials', function (Application $app, array $config) {
            $client = new S3Client([
                'version' => 'latest',
                'region' => $config['region'],
                'bucket' => $config['bucket'],
                'credentials' => false,
                'root' => $config['root'] ?? '',
                'visibility' => $config['visibility'] ?? Visibility::PRIVATE,
                'directory_separator' => $config['directory_separator'] ?? '/',
            ]);

            $visibility = new AwsS3PortableVisibilityConverter(
                $config['visibility'] ?? Visibility::PRIVATE
            );

            $adapter = new AwsS3V3Adapter(
                $client,
                $config['bucket'],
                $config['root'] ?? '',
                $visibility,
            );

            return  new LaravelAwsS3V3Adapter(
                new Filesystem($adapter),
                $adapter,
                $config,
                $client
            );
        });
    }
}
