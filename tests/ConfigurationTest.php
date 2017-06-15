<?php

/*
 * This file is part of Raven.
 *
 * (c) Sentry Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Raven\Tests;

use Raven\Configuration;
use Raven\Processor\RemoveCookiesProcessor;
use Raven\Processor\RemoveHttpBodyProcessor;
use Raven\Processor\SanitizeDataProcessor;
use Raven\Processor\SanitizeHttpHeadersProcessor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider optionsDataProvider
     */
    public function testConstructor($option, $value, $getterMethod)
    {
        $configuration = new Configuration([$option => $value]);

        $this->assertEquals($value, $configuration->$getterMethod());
    }

    /**
     * @dataProvider optionsDataProvider
     */
    public function testGettersAndSetters($option, $value, $getterMethod, $setterMethod = null)
    {
        $configuration = new Configuration();

        if (null !== $setterMethod) {
            $configuration->$setterMethod($value);
        }

        $this->assertEquals($value, $configuration->$getterMethod());
    }

    public function optionsDataProvider()
    {
        return [
            ['trust_x_forwarded_proto', false, 'isTrustXForwardedProto', 'setIsTrustXForwardedProto'],
            ['prefixes', ['foo', 'bar'], 'getPrefixes', 'setPrefixes'],
            ['serialize_all_object', false, 'getSerializeAllObjects', 'setSerializeAllObjects'],
            ['curl_method', 'sync', 'getCurlMethod', 'setCurlMethod'],
            ['curl_path', 'curl', 'getCurlPath', 'setCurlPath'],
            ['curl_ipv4', true, 'getCurlIpv4', 'setCurlIpv4'],
            ['curl_ssl_version', CURL_SSLVERSION_DEFAULT, 'getCurlSslVersion', 'setCurlSslVersion'],
            ['sample_rate', 0.5, 'getSampleRate', 'setSampleRate'],
            ['install_default_breadcrumb_handlers', false, 'shouldInstallDefaultBreadcrumbHandlers', 'setInstallDefaultBreadcrumbHandlers'],
            ['install_shutdown_handler', false, 'shouldInstallShutdownHandler', 'setInstallShutdownHandler'],
            ['mb_detect_order', null, 'getMbDetectOrder', 'setMbDetectOrder'],
            ['auto_log_stacks', false, 'getAutoLogStacks', 'setAutoLogStacks'],
            ['context_lines', 3, 'getContextLines', 'setContextLines'],
            ['current_environment', 'foo', 'getCurrentEnvironment', 'setCurrentEnvironment'],
            ['environments', ['foo', 'bar'], 'getEnvironments', 'setEnvironments'],
            ['excluded_loggers', ['bar', 'foo'], 'getExcludedLoggers', 'setExcludedLoggers'],
            ['excluded_exceptions', ['foo', 'bar', 'baz'], 'getExcludedExceptions', 'setExcludedExceptions'],
            ['excluded_app_paths', ['foo', 'bar'], 'getExcludedProjectPaths', 'setExcludedProjectPaths'],
            ['project_root', 'baz', 'getProjectRoot', 'setProjectRoot'],
            ['logger', 'foo', 'getLogger', 'setLogger'],
            ['open_timeout', 2, 'getOpenTimeout', 'setOpenTimeout'],
            ['timeout', 3, 'getTimeout', 'setTimeout'],
            ['proxy', 'tcp://localhost:8125', 'getProxy', 'setProxy'],
            ['release', 'dev', 'getRelease', 'setRelease'],
            ['server_name', 'foo', 'getServerName', 'setServerName'],
            ['ssl', [], 'getSslOptions', 'setSslOptions'],
            ['ssl_verification', false, 'isSslVerificationEnabled', 'setSslVerificationEnabled'],
            ['ssl_ca_file', 'path/to/file', 'getSslCaFile', 'setSslCaFile'],
            ['tags', ['foo', 'bar'], 'getTags', 'setTags'],
            ['error_types', 0, 'getErrorTypes', 'setErrorTypes'],
            ['processors', [SanitizeDataProcessor::class, RemoveCookiesProcessor::class, RemoveHttpBodyProcessor::class, SanitizeHttpHeadersProcessor::class], 'getProcessors', 'setProcessors'],
            ['processors_options', ['foo' => 'bar'], 'getProcessorsOptions', 'setProcessorsOptions'],
        ];
    }

    /**
     * @dataProvider serverOptionDataProvider
     */
    public function testServerOption($dsn, $options)
    {
        $configuration = new Configuration(['server' => $dsn]);

        $this->assertEquals($options['project_id'], $configuration->getProjectId());
        $this->assertEquals($options['public_key'], $configuration->getPublicKey());
        $this->assertEquals($options['secret_key'], $configuration->getSecretKey());
        $this->assertEquals($options['server'], $configuration->getServer());
    }

    public function serverOptionDataProvider()
    {
        return [
            [
                'http://public:secret@example.com/1',
                [
                    'project_id' => 1,
                    'public_key' => 'public',
                    'secret_key' => 'secret',
                    'server' => 'http://example.com',
                ],
            ],
            [
                'http://public:secret@example.com:80/1',
                [
                    'project_id' => 1,
                    'public_key' => 'public',
                    'secret_key' => 'secret',
                    'server' => 'http://example.com',
                ],
            ],
            [
                'https://public:secret@example.com/1',
                [
                    'project_id' => 1,
                    'public_key' => 'public',
                    'secret_key' => 'secret',
                    'server' => 'https://example.com',
                ],
            ],
            [
                'https://public:secret@example.com:443/1',
                [
                    'project_id' => 1,
                    'public_key' => 'public',
                    'secret_key' => 'secret',
                    'server' => 'https://example.com',
                ],
            ],
            [
                'http://public:secret@example.com/sentry/1',
                [
                    'project_id' => 1,
                    'public_key' => 'public',
                    'secret_key' => 'secret',
                    'server' => 'http://example.com/sentry',
                ],
            ],
            [
                'http://public:secret@example.com:3000/sentry/1',
                [
                    'project_id' => 1,
                    'public_key' => 'public',
                    'secret_key' => 'secret',
                    'server' => 'http://example.com:3000/sentry',
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidServerOptionDataProvider
     *
     * @expectedException \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @expectedExceptionMessageRegExp /^The option "server" with value "(.*)" is invalid.$/
     */
    public function testServerOptionsWithInvalidServer($dsn)
    {
        new Configuration(['server' => $dsn]);
    }

    public function invalidServerOptionDataProvider()
    {
        return [
            ['http://public:secret@/1'],
            ['http://public:secret@example.com'],
            ['http://:secret@example.com/1'],
            ['http://public@example.com/1'],
            ['tcp://public:secret@example.com/1'],
        ];
    }

    public function testShouldCapture()
    {
        $configuration = new Configuration();

        $this->assertTrue($configuration->shouldCapture());

        $configuration->setCurrentEnvironment('foo');
        $configuration->setEnvironments(['bar']);

        $this->assertFalse($configuration->shouldCapture());

        $configuration->setCurrentEnvironment('foo');
        $configuration->setEnvironments(['foo']);

        $this->assertTrue($configuration->shouldCapture());

        $configuration->setEnvironments([]);

        $this->assertTrue($configuration->shouldCapture());

        $configuration->setShouldCapture(function ($value) {
            return false;
        });

        $this->assertTrue($configuration->shouldCapture());

        $data = 'foo';

        $this->assertFalse($configuration->shouldCapture($data));
    }
}