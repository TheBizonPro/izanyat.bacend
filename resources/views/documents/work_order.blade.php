<html>
	<body>
		<h3 align="center">ЗАКАЗ-НАРЯД №{{ $task->id }}</h3>
		<p align="right">{{$task->created_date}}</p>

		<p>
			{{ $task->project->company->name }}, именуемое в дальнейшем «Заказчик», в лице директора  заказывает у Плательщика налога на профессиональный доход {{ $task->user->full_name }} (ИНН {{ $task->user->inn }}), именуемый в дальнейшем «Исполнитель»  выполнить следующие работы:
		</p>

		<table width="100%" border="1" cellspacing="0" cellpadding="2">
			<thead>
				<tr>
					<th>№ п/п</th>
					<th>Название заказанных работ</th>
					<th>Объем</th>
					<th>Срок выполнения</th>
					<th>Цена</th>
					<th>Сумма</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>1</td>
					<td>{{ $task->name }}</td>
					<td>1</td>
					<td>{{ \Illuminate\Support\Carbon::parse($task->date_from)->diffInDays($task->date_till) }}</td>
					<td>{{ $task->is_sum_confirmed ? "$task->sum руб." : "Договорная"  }} </td>
					<td>{{ $task->is_sum_confirmed ? "$task->sum руб." : "Договорная"  }}</td>
				</tr>
			</tbody>
		</table>

		<p align="right">Заказ-наряд принят исполнителем</p>
</body>
</html>
