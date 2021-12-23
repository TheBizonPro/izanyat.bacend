 <div class="row mb-3">
    <div class="col-6">
        <div class='text-muted'>Идентификатор платежа <span class="text-dark ms-2">{{$payout->id}}</span></div>
    </div>
    <div class="col-6">
        @if($payout->receipt_url!=null && $payout->status == 'canceled')
        <div class='text-muted'>Статус <span class="text-dark ms-2">Успешно</span></div>
        @else
        <div class='text-muted'>Статус <span class="text-dark ms-2">{{$payout->getTranslatedStatus()}}</span></div>
        @endif
    </div>
 </div>
 <div class="row mb-4">
     <div class="col">
         <div class='text-muted'>Дата перевода <span class="text-dark ms-2">{{$payout->getCreatedDateAttribute()}} в {{$payout->getCreatedTime()}}</span></div>
     </div>
 </div>
 <div class="row mb-3">
     <div class="col">
         <div class='text-muted'>Отправитель <span class="text-dark ms-2">{{$payout->task->company()->name}}</span></div>
     </div>
 </div>
 <div class="row mb-3">
     <div class="col">
         <div class='text-muted'>Получатель <span class="text-dark ms-2">{{$payout->user->name}}</span></div>
     </div>
 </div>
 <div class="row mb-3">
     <div class="col">
         <div class='text-muted'>Способ оплаты <span class="text-dark ms-2">На карту</span></div>
     </div>
 </div>
 <div class="row mb-3">
     <div class="col">
         <div class='text-muted'>Номер карты <span class="text-dark ms-2">{{$payout->user->bankAccount->hiddenCard()}}</span></div>
     </div>
 </div>
 <div class="row mb-3">
     <div class="col">
         <div class='text-muted'>Платежная система <span class="text-dark ms-2">НКО “МОБИ.Деньги”</span></div>
     </div>
 </div>
 <div class="row mb-4">
     <div class="col">
         <div class='text-muted'>Назначение платежа <span class="text-dark ms-2">Оплата за услугу #{{$payout->task->id}} "{{$payout->task->name}}" </span></div>
     </div>
 </div>
 <div class="row mb-4">
     <div class="col">
         <div class='text-muted'>Сумма <span class="text-dark ms-2">{{$payout->task->sum}} ₽</span></div>
     </div>
 </div>
 <div class="row mb-4">
     <div class="col">
             @if($payout->receipt_url!=null && $payout->status == 'complete')
                 <span class='text-muted me-3'>Чек</span>
                 <a target="_blank" href="{{$payout->getReceiptUrl(!config('fns.test_offline_mode'))}}" class="btn btn-sm btn-outline-primary btn-pill me-3" >
                     <div class="text">Открыть</div>
                     <div class="wait" hidden><b class="fad fa-spinner fa-pulse me-2"></b>Ожидайте</div>
                 </a>
                <button class="btn-annulate btn btn-sm btn-outline-danger btn-pill">
                     <div class="text">Аннулировать</div>
                     <div class="wait" hidden><b class="fad fa-spinner fa-pulse me-2"></b>Ожидайте</div>
                </button>
             @elseif($payout->receipt_url!=null && $payout->status == 'canceled')
                <div class='text-muted me-3'>Чек <span class="text-dark ms-2">Аннулирован</span>
                    <a target="_blank" href="{{$payout->getReceiptUrl(!config('fns.test_offline_mode'))}}" class="btn btn-sm btn-outline-primary btn-pill ms-2" >
                        <div class="text">Открыть</div>
                        <div class="wait" hidden><b class="fad fa-spinner fa-pulse me-2"></b>Ожидайте</div>
                    </a>
                </div>
             @else
                <div class='text-muted me-3'>Чек <span class="text-dark">-</span></div>
             @endif
     </div>
 </div>
 <div class="row mb-4">
     <div class="col">
        <div class='text-muted'>В случае если вы не согласны с представленной информацией, напишите в техподдержку партнера <a href="mailto: info@izanyat.ru">info@izanyat.ru</a></div>
    </div>
</div>
