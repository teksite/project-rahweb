<x-lareon::admin-list>
    @section('title', __('lareon::global.crud.titles.list',['attribute'=>__('tickets')]))

    @section('list')
        <x-lareon::table :rows="$tickets" :headers="['id'=>'#','title'=>__('title'),'status'=>__('status'),'created_at'=>__('created at') ,'user_id'=> __('creator')]">
            @foreach($tickets as $key=>$ticket)
                <tr>
                    <td class="p-3">{{$tickets->firstItem() + $key}}</td>
                    <td>{{$ticket->title}}</td>
                    <td>{{$ticket->status->label()}}</td>
                    <td>
                        <x-lareon::date :date="$ticket->created_at"/>
                    </td>
                    <td> {{$ticket->creator->fullname ?? '-'}} </td>
                    <td>
                        <x-lareon::action-box class="action">
                            <x-lareon::links.action type="edit" :href="route('admin.tickets.edit' , $ticket)" can="admin.ticket.edit"/>
                            <x-lareon::links.action type="delete" method="delete" :href="route('admin.tickets.destroy' , $ticket)" can="admin.ticket.delete"/>
                        </x-lareon::action-box>
                    </td>
                </tr>
            @endforeach
            <x-slot:foot>
                <tr>
                    <td colspan="9" class="p-2">
                        {!! $tickets->appends(request()->query())->links() !!}
                    </td>
                </tr>
            </x-slot:foot>
        </x-lareon::table>
    @endsection

</x-lareon::admin-list>

