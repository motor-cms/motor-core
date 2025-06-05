<?php

namespace Motor\Core\Services\Aws;

use Aws\Credentials\AssumeRoleCredentialProvider;
use Aws\Credentials\CredentialProvider;
use Aws\Sts\StsClient;

class AssumedRoleCredentialsTwo
{
    public static function create(): ?callable
    {
        $assumedRoleArn = config('services.s3.role_arn');

        if ($assumedRoleArn === null) {
            return null;
        }

        $assumeRoleCredentials = new AssumeRoleCredentialProvider([
            'client' => new StsClient([
                'region' => config('services.s3.region'),
                'version' => config('services.s3.version', 'latest'),
            ]),
            'assume_role_params' => [
                'RoleArn' => $assumedRoleArn,
                'WebIdentityTokenFile' => config('services.s3.token_file_2'),
                'RoleSessionName' => 'MotorCoreSession',
            ],
        ]);

        return CredentialProvider::memoize($assumeRoleCredentials);
    }
}
