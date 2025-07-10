<?php

declare(strict_types = 1);

require_once '/app/vendor/autoload.php';

use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
Use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\SdkBuilder;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SemConv\ResourceAttributes;
use OpenTelemetry\SDK\Common\Util\ShutdownHandler;

/**
 * Initializes the OpenTelemetry SDK and registers it globally.
 */
function initialize_opentelemetry(): void
{
    $resource = ResourceInfoFactory::defaultResource()->merge(ResourceInfo::create(Attributes::create([
        ResourceAttributes::SERVICE_NAME => 'api-php-template',
        ResourceAttributes::SERVICE_VERSION => '1.0.0',
    ])));

    $transport = new OtlpHttpTransportFactory()->create('http://otel-collector:4318/v1/traces', 'application/json');

    $spanProcessor = new BatchSpanProcessor(
        new SpanExporter($transport),
        Clock::getDefault()
    );

    $tracerProvider = new TracerProvider(
        $spanProcessor,
        new AlwaysOnSampler(),
        $resource
    );

    new SdkBuilder()
        ->setTracerProvider($tracerProvider)
        ->setPropagator(TraceContextPropagator::getInstance())
        ->buildAndRegisterGlobal();

    ShutdownHandler::register([$tracerProvider, 'shutdown']);
}