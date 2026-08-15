<x-lareon::admin-layout>
    @section('title', __('lareon::global.crud.titles.edit',['attribute'=>__('page') . " ($ticket->title)"]))
    @section('header.start')
        <x-lareon::links.nav :href="route('admin.tickets.index')" :content="__('lareon::global.buttons.all_attribute' ,['attribute'=>__('tickets')])" color="index"/>
    @endsection

    <section class="p-6 rounded-xl bordering grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <h2>
                {{$ticket->title}}
            </h2>
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
                            <div>
                                <div>
                                    <span class="text-xs font-bold">{{$item->admin->fullname}}</span>
                                    <span class="text-xs text-gray-600">({{$item->role->title}})</span>
                                </div>
                                {{$item->review ?? ''}}

                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div>
            <div class="flex items-center justify-start gap-3">
                  <span>
                    {{__('attachment')}}
              </span>
                <x-lareon::links.action type="show" href="{{$ticket->file}}" can="admin.ticket.read"/>
            </div>
            <iframe class="w-full" height="400" src="{{$ticket->file}}"></iframe>
        </div>
    </section>


</x-lareon::admin-layout>
