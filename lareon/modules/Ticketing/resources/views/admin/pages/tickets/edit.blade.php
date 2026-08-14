<x-lareon::admin-editor :action="route('admin.tickets.update' , $ticket)" method="update" :instance="$ticket" >
    @section('title', __('lareon::global.crud.titles.edit',['attribute'=>__('page') . " ($ticket->title)"]))
    @section('header.start')
        <x-lareon::links.nav :href="route('admin.tickets.index')" :content="__('lareon::global.buttons.all_attribute' ,['attribute'=>__('tickets')])" color="index"/>
    @endsection

    @section('form')
        <x-lareon::editor.tabs.item :title="__('content')">
            <div class="space-y-6">
                <x-lareon::editor.input :required="true" labelPosition="start" :label="__('title')" name="title" :value="$ticket->title" :placeholder="__('lareon::global.placeholders.write.two',['attribute'=>__('title') , 'item'=>__('page')])"/>
                <x-lareon::editor.input-slug :required="true" labelPosition="start" :label="__('slug')" :value="$ticket->slug" :placeholder="__('lareon::global.placeholders.write.unique.two',['attribute'=>__('slug') , 'item'=>__('page')])"/>
            </div>

            <div class="space-y-6 y-box">
                <x-lareon::editor.input-textarea :required="false" :label="__('excerpt')" name="excerpt" :placeholder="__('lareon::global.placeholders.write.one',['attribute'=>__('excerpt')])">{!! $ticket->excerpt !!}</x-lareon::editor.input-textarea>
                <x-lareon::editor.section.input-editor rows="9" :required="false" :label="__('body')" name="body" :placeholder="__('lareon::global.placeholders.write.one',['attribute'=>__('body')])">{!! $ticket->body!!}</x-lareon::editor.section.input-editor>
            </div>

            <x-slot:aside>
            </x-slot:aside>

        </x-lareon::editor.tabs.item>
    @endsection

</x-lareon::admin-editor>
