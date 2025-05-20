<?php

namespace Motor\Core\Services;

use Aws\Credentials\AssumeRoleCredentialProvider;
use Aws\Credentials\CredentialProvider;
use Aws\Sts\StsClient;

class AssumedRoleCredentials
{
    public static function create(): ?callable
    {
        $assumedRoleArn = config('services.aws.primary_account_assumed_role_arn');

        if ($assumedRoleArn === null) {
            return null;
        }

        $assumeRoleCredentials = new AssumeRoleCredentialProvider([
            'client' => new StsClient([
                'region' => 'us-east-1',
                'version' => '2011-06-15',
            ]),
            'assume_role_params' => [
                'RoleArn' => $assumedRoleArn,
                'RoleSessionName' => 'vapor-account',
            ],
        ]);

        return CredentialProvider::memoize($assumeRoleCredentials);
    }
}
