@props([
    'id' => 'no-editor-form',
    'hasTab' => true,
    'hasMeta' => true,
    'hasSeo' => true,
])
@php
    $hasAside = \Illuminate\Support\Facades\View::hasSection('aside') || (!$hasTab && ($showPublishSection['publishInfo'] || $showPublishSection['publishStatus']));
    $styleClass = $hasAside ? 'flex flex-col lg:flex-row gap-6' : '';
@endphp

<x-lareon::admin-layout>
    <x-slot:title> @yield('title') </x-slot:title>
    <x-slot:description> @yield('description') </x-slot:description>

    @yield('form.before')

    <div id="{{ $id }}" class="inner-content">
        @csrf
        @if($realMethod)
            @method($realMethod)
        @endif
        <input type="hidden" name="model" value="{{encrypt(get_class($instance))}}">
        <input type="hidden" name="model_key" value="{{encrypt($instance?->getKey())}}">
        @yield('form.start')

        <div class="{{ $styleClass }}">
            <div class="w-full space-y-6">
                @hasSection('form')
                    <x-lareon::editor.tabs.layout :hasTab="$hasTab">
                        @yield('form')

                        @if($hasMeta && $showTemplateSection)
                            <x-lareon::editor.tabs.item :title="__('meta data')">
                                <x-meta::elements-loader :template="$instance->template" :value="$instance->metaData"/>
                            </x-lareon::editor.tabs.item>
                        @endif

                        @if($hasSeo && $showSeoSection)
                            <x-lareon::editor.tabs.item :title="__('seo')">
                                <x-lareon::editor.tabs.section class="">
                                    <x-seo::editor.seo-section :instance="$instance" :value="$instance->seo()"/>
                                </x-lareon::editor.tabs.section>
                            </x-lareon::editor.tabs.item>
                        @endif

                        @if(($showPublishSection['publishInfo']) || ($showPublishSection['publishStatus']))
                            <x-lareon::editor.tabs.item :title="__('publish data')">
                                <div @class(['grid gap-6 lg:grid-cols-2' => $showPublishSection['publishInfo'] && $showPublishSection['publishStatus']])>
                                    @if($showPublishSection['publishInfo'] ?? false)
                                        <x-lareon::editor.tabs.section>
                                            <x-lareon::editor.section.publish-status :instance="$instance"/>
                                        </x-lareon::editor.tabs.section>
                                    @endif
                                    @if($showPublishSection['publishStatus'] ?? false)
                                        <x-lareon::editor.tabs.section>
                                            <x-lareon::editor.section.publish-info :instance="$instance"/>
                                        </x-lareon::editor.tabs.section>
                                    @endif
                                </div>
                            </x-lareon::editor.tabs.item>
                        @endif
                    </x-lareon::editor.tabs.layout>
                @endif
            </div>

            @if($hasAside)
                <aside class="w-full lg:max-w-[350px]">
                    <div class="sticky top-6 space-y-6">
                        @hasSection('aside')
                            @yield('aside')
                        @endif
                        @if(($showPublishSection['publishInfo']) || ($showPublishSection['publishStatus']))
                            @if($showPublishSection['publishInfo'] ?? false)
                                <x-lareon::editor.tabs.section>
                                    <x-lareon::editor.section.publish-status :instance="$instance"/>
                                </x-lareon::editor.tabs.section>
                            @endif
                            @if($showPublishSection['publishStatus'] ?? false)
                                <x-lareon::editor.tabs.section>
                                    <x-lareon::editor.section.publish-info :instance="$instance"/>
                                </x-lareon::editor.tabs.section>
                            @endif
                        @endif
                    </div>
                </aside>
            @endif
        </div>

        @yield('form.end')

    </div>

    @yield('form.after')
</x-lareon::admin-layout>
