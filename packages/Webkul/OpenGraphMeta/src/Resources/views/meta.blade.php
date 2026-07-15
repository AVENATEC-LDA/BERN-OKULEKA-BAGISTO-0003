@php
    $service = app(\Webkul\OpenGraphMeta\Services\OpenGraphMetaService::class);
@endphp

{!! $service->render() !!}
