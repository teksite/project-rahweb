<x-lareon::admin-editor method="create" :action="route('admin.pages.store')" :instance="$page">
    @section('title', __('lareon::global.crud.titles.create',['attribute'=>__('page')]))
    @section('header.start')
        <x-lareon::links.nav :href="route('admin.pages.index')" :content="__('lareon::global.buttons.all_attribute' ,['attribute'=>__('pages')])" color="index"/>
    @endsection
    @section('form')
        <x-lareon::editor.tabs.item :title="__('content')">
            <div class="space-y-6">
                <x-lareon::editor.input :required="true" labelPosition="start" :label="__('title')" name="title" :placeholder="__('lareon::global.placeholders.write.two',['attribute'=>__('title') , 'item'=>__('page')])"/>
                <x-lareon::editor.input-slug :required="true" labelPosition="start" :label="__('slug')" :placeholder="__('lareon::global.placeholders.write.unique.two',['attribute'=>__('slug') , 'item'=>__('page')])"/>
            </div>

            <x-lareon::editor.tabs.section>
                <x-lareon::editor.input-textarea :required="false" :label="__('excerpt')" name="excerpt" :placeholder="__('lareon::global.placeholders.write.one',['attribute'=>__('excerpt')])"></x-lareon::editor.input-textarea>
                <x-lareon::editor.section.input-editor rows="9" :required="false" :label="__('body')" name="body" :placeholder="__('lareon::global.placeholders.write.one',['attribute'=>__('body')])"></x-lareon::editor.section.input-editor>
            </x-lareon::editor.tabs.section>

            <x-slot:aside>
                <x-lareon::editor.input-image :required="false" wrapperMode="y-box" name="primary_media_id"/>
                <x-lareon::editor.section.template type="page" :required="false" wrapperMode="y-box" :value="old('template')"/>
            </x-slot:aside>
        </x-lareon::editor.tabs.item>
    @endsection

</x-lareon::admin-editor>
