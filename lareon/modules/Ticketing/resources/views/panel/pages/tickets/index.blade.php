<x-lareon::admin-list>
    @section('title', __('lareon::global.crud.titles.list',['attribute'=>__('pages')]))
    @section('description', __('pages are fixed content on a website that rarely changes and displays the same information to visitors'))
    @section('header.start')
        <x-lareon::links.nav :href="route('admin.pages.create')" :content="__('lareon::global.buttons.new_one')" color="create" can="admin.page.create"/>
        <x-lareon::links.nav :href="route('admin.pages.trash.index')" :content="$trashCount" color="trash" can="admin.page.delete"/>
    @endsection
    @section('list')
        <x-lareon::table :rows="$pages" :headers="['id'=>'#',__('image') ,'title'=>__('title') ,__('publish status'),'created_at'=>__('created at') ,'published_at'=>__('published at') ,'']">
            @foreach($pages as $key=>$page)
                <tr>
                    <td class="p-3">{{$pages->firstItem() + $key}}</td>
                    <td>
                        <x-lareon::media-placeholder src="{{$page->primaryMedia?->url}}" alt="{{$page->title}}" type="image"/>
                    </td>
                    <td>{{$page->title}}</td>
                    <td>{!! $page->publish_status->toHtml()!!}</td>
                    <td>
                        <x-lareon::date :date="$page->created_at"/>
                    </td>
                    <td>
                        <x-lareon::date :date="$page->published_at"/>
                    </td>
                    <td>
                        <x-lareon::action-box class="action">
                            @if(\Illuminate\Support\Facades\Route::has('admin.pages.meta.edit'))
                                <x-lareon::links.action type="sub" :href="route('admin.pages.meta.edit' , $page)"/>
                            @endif
                            @if($page->path())
                                <x-lareon::links.action type="show" :href="$page->path()"/>
                            @endif
                            @if(\Illuminate\Support\Facades\Route::has('admin.pages.acl.edit'))
                                <x-lareon::links.action type="setting" :href="route('admin.pages.acl.edit' , $page)" can="admin.page.acl.edit"/>
                            @endif
                            <x-lareon::links.action type="edit" :href="route('admin.pages.edit' , $page)" can="admin.page.edit"/>
                            <x-lareon::links.action type="delete" method="delete" :href="route('admin.pages.destroy' , $page)" can="admin.page.delete"/>
                        </x-lareon::action-box>
                    </td>
                </tr>
            @endforeach
            <x-slot:foot>
                <tr>
                    <td colspan="9" class="p-2">
                        {!! $pages->appends(request()->query())->links() !!}
                    </td>
                </tr>
            </x-slot:foot>
        </x-lareon::table>

    @endsection

</x-lareon::admin-list>
