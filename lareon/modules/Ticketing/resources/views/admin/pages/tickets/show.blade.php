<x-lareon::admin-layout>
    @section('title', __('lareon::global.crud.titles.edit',['attribute'=>__('page') . " ($ticket->title)"]))
    @section('header.start')
        <x-lareon::links.nav :href="route('admin.tickets.index')" :content="__('lareon::global.buttons.all_attribute' ,['attribute'=>__('tickets')])" color="index"/>
    @endsection

    <section class="p-6 rounded-xl bordering">
        <h2>
            {{$ticket->title}}
        </h2>

        <div>
            <p>
                {{$ticket->body}}
            </p>
            <hr class="border-line_light my-3">
            <div class="space-y-3">
                       <span class="block">
                            {{__('creator')}}: {{$ticket->creator->fullname}}
                       </span>
                <ul class="space-y-3">
                    @foreach($ticket->approvals as $item)
                        <li class="flex gap-1 items-center justify-start">
                            {!! $item->status->toHtml() !!}
                            <span class="text-xs font-bold">{{$item->admin->fullname}}</span>
                            <span class="text-xs text-gray-600">({{$item->role->title}})</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>


</x-lareon::admin-layout>
