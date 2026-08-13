@props([
'type' => 'create', // create, edit, update, delete, restore
'instance' => null,
'publishStatus' => true,
'action' => null,
'method' => null,
'hasTab' => true,
'id' => 'editor-form',
'hasFile' => false,
'buttonText'=>null
])

@php
    $type = strtolower(trim($type));

    $httpMethods = [
        'create' => 'POST',
        'store' => 'POST',
        'edit' => 'PATCH',
        'update' => 'PUT',
        'patch' => 'PATCH',
        'delete' => 'DELETE',
        'destroy' => 'DELETE',
        'restore' => 'POST',
    ];

    $method = $method ?? $httpMethods[$type] ?? 'POST';
    $isEditMode = in_array($type, ['edit', 'update', 'patch']);
    $isDeleteMode = in_array($type, ['delete', 'destroy']);
    $isCreateMode = in_array($type, ['create', 'store']);

    $buttonColor = match(true) {
        $isDeleteMode => 'delete',
        $isEditMode => 'update',
        default => 'create'
    };

    $buttonText = $buttonText ?? match(true) {
        $isDeleteMode => trans('lareon::global.buttons.delete'),
        $isEditMode => trans('lareon::global.buttons.update'),
        default => trans('lareon::global.buttons.create')
    };

    $buttonIcon = match(true) {
        $isDeleteMode => 'trash',
        $isEditMode => 'pen',
        default => 'plus'
    };


    $styleClass=config('lareon.admin.layout.editor')=== 'two_column' ? 'md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-7' : '';
@endphp

<x-lareon::panel-layout>
    <x-slot:title>
        @yield('title')
    </x-slot:title>

    @yield('form.before')

    <form id="{{ $id }}" class="inner-content" method="{{ $method === 'GET' ? 'GET' : 'POST' }}" action="{{ $action ?? url()->current() }}" {{$hasFile ?  'enctype=multipart/form-data' : ''}}>
        @csrf
        @method($method)
        <div class="grid grid-cols-1 gap-6 {{$styleClass}} ">
            <div class="md:col-span-2 lg:col-span-2 xl:col-span-5">
                <div class="space-y-6">
                    @yield('form.start')
                    <div class="space-y-6">
                        @hasSection('form')
                            @if($hasTab)
                                <x-lareon::editor.tabs.layout>
                                    @yield('form')
                                    @if($publishStatus && !$isDeleteMode && $instance)
                                        <x-lareon::editor.tabs.item :title="__('publish data')">
                                            <x-lareon::editor.section.publish-info :instance="$instance"/>
                                        </x-lareon::editor.tabs.item>
                                    @endif
                                </x-lareon::editor.tabs.layout>
                            @else
                                @yield('form')
                            @endif
                        @endif
                    </div>
                    @yield('form.end')
                </div>
            </div>

            <aside class="xl:col-span-2">
                <div class="sticky top-6 space-y-6">
                    <div class="mt-6">
                        <x-lareon::buttons.nav :fullWidth="false" type="submit" role="submit" :color="$buttonColor" :icon="$buttonIcon">
                            {{ __($buttonText)}}
                        </x-lareon::buttons.nav>
                    </div>
                </div>
            </aside>
        </div>
    </form>
    @yield('form.after')

</x-lareon::panel-layout>
