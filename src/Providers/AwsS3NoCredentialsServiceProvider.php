<?php

namespace Motor\Core\Providers;

use Motor\Core\Services\AssumedRoleCredentials;
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
            ]);

            $adapter = new AwsS3V3Adapter(
                $client,
                $config['bucket']
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
