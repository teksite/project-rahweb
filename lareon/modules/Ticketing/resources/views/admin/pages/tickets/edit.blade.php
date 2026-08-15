@php use Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum; @endphp
<x-lareon::admin-editor :action="route('admin.tickets.update' , $ticket)" method="update" :instance="$ticket" :editor="!!$approval">
    @section('title', __('lareon::global.crud.titles.edit',['attribute'=>__('page') . " ($ticket->title)"]))
    @section('header.start')
        <x-lareon::links.nav :href="route('admin.tickets.index')" :content="__('lareon::global.buttons.all_attribute' ,['attribute'=>__('tickets')])" color="index"/>
    @endsection

    @section('form')
        <x-lareon::editor.tabs.item :title="__('content')">
            <x-lareon::editor.tabs.section>
                <h2>
                    {{$ticket->title}}
                </h2>

                <div >
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
            </x-lareon::editor.tabs.section>

            <x-slot:aside>
                @if($approval)
                    <x-lareon::editor.tabs.section>
                        <x-lareon::editor.input-select name="status" :label="__('change to')" :value="$approval->status->value">
                            @foreach(TicketStatusEnum::cases() as $case)
                                @continue(in_array($case , [TicketStatusEnum::PENDING,  TicketStatusEnum::IN_REVIEW ]))
                                <option value="{{$case->value}}">
                                    {{$case->label()}}
                                </option>
                            @endforeach
                        </x-lareon::editor.input-select>
                        <x-lareon::editor.input-textarea :required="false" :label="__('review')" name="review" :placeholder="__('lareon::global.placeholders.write.one',['attribute'=>__('review')])">{!! $approval->review !!}</x-lareon::editor.input-textarea>
                    </x-lareon::editor.tabs.section>
                @endif
            </x-slot:aside>

        </x-lareon::editor.tabs.section>
    @endsection

</x-lareon::admin-editor>
