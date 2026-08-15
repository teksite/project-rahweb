<?php

namespace Lareon\Steward\App\View\Components;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;
use Lareon\Modules\Meta\App\Traits\HasTemplate;
use Lareon\Modules\Page\App\Models\Page;
use Lareon\Modules\Seo\App\Traits\HasSeo;

class AdminEditor extends Component
{
    const array HttpMethods = [
        'create'  => 'POST',
        'store'   => 'POST',
        'restore' => 'POST',
        'post'    => 'POST',

        'edit'   => 'PATCH',
        'patch'  => 'PATCH',
        'update' => 'PATCH',

        'put' => 'PUT',

        'delete'  => 'DELETE',
        'destroy' => 'DELETE',

        'get' => 'GET',
    ];


    public readonly string $methodType;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public bool    $editor = true,
        public ?string $method = null,
        public ?string $action = null,
        public bool    $hasFile = false,
        public mixed   $instance = null,
    )
    {
        $this->methodType = strtolower($this->method);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $data = [
            'editor'              => $this->instance,
            'instance'            => $this->instance,
            'formAttributes'      => $this->buildFormAttributes(),
            'realMethod'          => $this->buildRealMethod(),
            'showTemplateSection' => $this->showTemplateSection(),
            'showSeoSection'      => $this->showSeoSection(),
            'showPublishSection'  => $this->showPublishSection(),

            ...$this->resolveButtonPresentation(),
        ];
        return $this->editor
            ? view('lareon::admin.layouts.editor', $data)
            : view('lareon::admin.layouts.no-editor', $data);
    }


    private function buildFormAttributes(): string
    {

        $html_method = in_array($this->methodType, array_keys(Arr::except(self::HttpMethods, ['get']))) ? 'POST' : 'GET';

        $finalAttribute = removeNullValues([
            'method'  => $html_method,
            'action'  => $this->action,
            'enctype' => $this->hasFile ? 'multipart/form-data' : null,
        ]);

        $attributes = '';

        foreach ($finalAttribute as $key => $value) {
            $attributes .= " $key=$value ";
        }
        return $attributes;
    }

    private function buildRealMethod(): ?string
    {
        $real_method = self::HttpMethods[$this->methodType] ?? "POST";
        return !in_array($real_method, ['GET', 'POST']) ? $real_method : null;
    }

    private function resolveButtonPresentation(): array
    {
        return match (true) {
            in_array($this->methodType, ['delete', 'destroy'], true)              => [
                'buttonColor'   => 'delete',
                'buttonTextKey' => trans('lareon::global.buttons.delete'),
                'buttonIcon'    => 'trash',
            ],

            in_array($this->methodType, ['edit', 'update', 'patch', 'put'], true) => [
                'buttonColor'   => 'update',
                'buttonTextKey' => trans('lareon::global.buttons.update'),
                'buttonIcon'    => 'pen',
            ],
            default                                                               => [
                'buttonColor'   => 'create',
                'buttonTextKey' => trans('lareon::global.buttons.create'),
                'buttonIcon'    => 'plus',
            ],
        };
    }


    private function showTemplateSection(): bool
    {
        return !!$this->instance && in_array(HasTemplate::class, array_keys(class_uses_recursive($this->instance))) && count($this->instance->metaData) > 0;
    }

    private function showSeoSection(): bool
    {
        return !!$this->instance && in_array(HasSeo::class, array_keys(class_uses_recursive($this->instance)));
    }

    private function showPublishSection(): array
    {
        if ($this->instance === null) return [
            'publishInfo'   => false,
            'publishStatus' => false,
        ];

        return [
            'publishInfo'   => $this->instance->hasAttribute('publish_status'),
            'publishStatus' => $this->instance->hasAttribute('published_at'),
        ];

    }
}
