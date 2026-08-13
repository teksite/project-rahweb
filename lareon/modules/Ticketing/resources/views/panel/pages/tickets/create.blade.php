<x-lareon::panel-editor type="create" method="'POST'" :action="route('panel.tickets.store')">
    @section('form')
        <section class="">
            <x-lareon::box type="y" class="space-y-3 xl:col-span-3">
                <div class="w-full md:w-1/2">
                    <x-lareon::editor.input :required="true" labelPosition="top" :label="__('title')" name="title"/>
                </div>
                <x-lareon::editor.input-textarea rows="16" :required="true" labelPosition="top" :label="__('description')" name="body"></x-lareon::editor.input-textarea>
            </x-lareon::box>
        </section>
    @endsection
</x-lareon::admin-editor>
