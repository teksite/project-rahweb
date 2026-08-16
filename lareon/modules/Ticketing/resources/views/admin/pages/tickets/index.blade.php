<x-lareon::admin-list>
    @section('title', __('lareon::global.crud.titles.list',['attribute'=>__('tickets')]))

    @section('list')
        <x-lareon::table :rows="$tickets" :headers="['id'=>'#','title'=>__('title'),'status'=>__('status'),'created_at'=>__('created at') ,'user_id'=> __('creator')]">
            @foreach($tickets as $key=>$ticket)
                <tr>
                    <td class="p-3">{{$tickets->firstItem() + $key}}</td>
                    <td>{{$ticket->title}}</td>
                    <td>
                        <div class="">
                            @foreach($ticket->approvals as $approval)
                                <div class="flex  w-fit min-w-fit text-xs text-gray-600">
                                    {!! $approval->status->toHtml() !!}
                                    <span>
                                        {{$approval->admin->fullname}} ({{$approval->role->title}})
                                    </span>
                                </div>
                            @endforeach
                        </div>

                    </td>
                    <td>
                        <x-lareon::date :date="$ticket->created_at"/>
                    </td>
                    <td> {{$ticket->creator->fullname ?? '-'}} </td>
                    <td>
                        <x-lareon::action-box class="action">
                            <x-lareon::links.action type="show" :href="route('admin.tickets.show' , $ticket)" can="admin.ticket.read"/>
                            <x-lareon::links.action type="edit" :href="route('admin.tickets.edit' , $ticket)" can="admin.ticket.edit"/>
                            <x-lareon::links.action type="delete" method="delete" :href="route('admin.tickets.destroy' , $ticket)" can="admin.ticket.delete"/>
                        </x-lareon::action-box>
                    </td>
                </tr>
            @endforeach
            <x-slot:foot>
                @canany('admin.ticket.edit')

                    <tr>
                        <td colspan="9" class="p-2">
                            <form method="POST" action="{{route('admin.tickets.index')}}" class="flex items-center gap-1">
                                @method('PATCH')
                                <x-lareon::editor.input-select :label="__('bulk action')" name="action" labelPosition="start">
                                    <option value="">none</option>
                                    <option value="review">{{__('review all')}}</option>
                                    <option value="approve">{{__('approve all')}}</option>
                                    <option value="reject">{{__('reject all')}}</option>
                                </x-lareon::editor.input-select>
                                <x-lareon::buttons.nav class="min-w-36" :fullWidth="false" type="submit" color="update" size="xs">
                                    {{ __('do') }}
                                </x-lareon::buttons.nav>
                            </form>
                        </td>
                    </tr>
                @endcan
                <tr>
                    <td colspan="9" class="p-2">
                        {!! $tickets->appends(request()->query())->links() !!}
                    </td>
                </tr>
            </x-slot:foot>
        </x-lareon::table>
    @endsection

</x-lareon::admin-list>

