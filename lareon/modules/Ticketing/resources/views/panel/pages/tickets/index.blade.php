<x-lareon::panel-layout>
    @section('title', __('lareon::global.crud.titles.list',['attribute'=>__('tickets')]))
    <div class="flex items-center justify-end">
        <div class="w-64 my-6">
            <x-lareon::links.nav :href="route('panel.tickets.create')" :content="__('lareon::global.buttons.new_one')" color="create" can="panel.ticket.create"/>
        </div>
    </div>
    <x-lareon::table :rows="$tickets" :headers="['id'=>'#','title'=>__('title'),'created_at'=>__('created at')]">

        @foreach($tickets as $key=>$ticket)
            <tr>
                <td class="p-3">{{$tickets->firstItem() + $key}}</td>
                <td>
                    <x-lareon::media-placeholder src="{{$ticket->primaryMedia?->url}}" alt="{{$ticket->title}}" type="image"/>
                </td>
                <td>{{$ticket->title}}</td>
                <td>{!! $ticket->status->value !!}</td>
                <td>
                    <x-lareon::date :date="$ticket->created_at"/>
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


</x-lareon::panel-layout>>
