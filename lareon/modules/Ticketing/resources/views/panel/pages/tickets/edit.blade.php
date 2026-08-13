<x-lareon::admin-editor :action="route('admin.pages.update' , $page)" method="update" :instance="$page" >
    @section('title', __('lareon::global.crud.titles.edit',['attribute'=>__('page') . " ($page->title)"]))
    @section('header.start')
        <x-lareon::links.nav :href="route('admin.pages.index')" :content="__('lareon::global.buttons.all_attribute' ,['attribute'=>__('pages')])" color="index"/>
        <x-lareon::links.nav :href="route('admin.pages.create')" :content="__('lareon::global.buttons.new_one')" color="create" can="admin.page.create"/>
    @endsection
    @section('header.end')
        <x-lareon::links.action type="delete" :href="route('admin.pages.destroy', $page)" method="delete"  :label="trans('lareon::global.buttons.delete')" can="admin.page.delete"/>

    @endsection

    @section('form')
        <x-lareon::editor.tabs.item :title="__('content')">
            <div class="space-y-6">
                <x-lareon::editor.input :required="true" labelPosition="start" :label="__('title')" name="title" :value="$page->title" :placeholder="__('lareon::global.placeholders.write.two',['attribute'=>__('title') , 'item'=>__('page')])"/>
                <x-lareon::editor.input-slug :required="true" labelPosition="start" :label="__('slug')" :value="$page->slug" :placeholder="__('lareon::global.placeholders.write.unique.two',['attribute'=>__('slug') , 'item'=>__('page')])"/>
            </div>

            <div class="space-y-6 y-box">
                <x-lareon::editor.input-textarea :required="false" :label="__('excerpt')" name="excerpt" :placeholder="__('lareon::global.placeholders.write.one',['attribute'=>__('excerpt')])">{!! $page->excerpt !!}</x-lareon::editor.input-textarea>
                <x-lareon::editor.section.input-editor rows="9" :required="false" :label="__('body')" name="body" :placeholder="__('lareon::global.placeholders.write.one',['attribute'=>__('body')])">{!! $page->body!!}</x-lareon::editor.section.input-editor>
            </div>

            <x-slot:aside>
                <x-lareon::editor.input-image :required="false" wrapperMode="y-box" :value="$page->primaryMedia?->id" name="primary_media_id"/>
                <x-lareon::editor.section.template type="page" :required="false" wrapperMode="y-box" :value="old('template' , $page->template_id ?? null)"/>
            </x-slot:aside>

        </x-lareon::editor.tabs.item>
    @endsection

</x-lareon::admin-editor>
