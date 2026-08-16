<x-lareon::admin-list>
    @section('title', __('lareon::global.crud.titles.list',['attribute'=>__('tickets')]))

    @section('list')
        <x-lareon::table :rows="$items" :headers="['id'=>'#','title'=>__('title'),'user.name'=>__('creator'),'sent_at'=>__('sent at'),'attempt'=>__('attempt'),'competed_at'=> __('competed at') ,'status'=> __('status')]">
            @foreach($items as $key=>$item)
                <tr>
                    <td class="p-3">{{$items->firstItem() + $key}}</td>
                    <td>{{$item->ticket->title}}</td>
                    <td> {{$item->ticket->creator->fullname ?? '-'}} </td>

                    <td>
                        <x-lareon::date :date="$item->created_at"/>
                    </td>
                    <td>
                        {{$item->attempt}}
                    </td>
                    <td>
                        <x-lareon::date :date="$item->completed_at"/>
                    </td>  <td>
                        {{$item->status->label()}}
                    </td>

                </tr>
            @endforeach
            <x-slot:foot>
                <tr>
                    <td colspan="9" class="p-2">
                        {!! $items->appends(request()->query())->links() !!}
                    </td>
                </tr>
            </x-slot:foot>
        </x-lareon::table>
    @endsection

</x-lareon::admin-list>

