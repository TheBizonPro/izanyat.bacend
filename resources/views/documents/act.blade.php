<html>
	<body>
		<h3 align="center">АКТ</h3>
		<h4 align="center">сдачи-приемки выполненных работ №{{ $task->id }}</h4>
		<p align="right">{{ \Carbon\Carbon::parse($task->created_at)->format('d.m.Y') }}</p>

		<p>
			{{ $task->project->company->name }}, именуемое в дальнейшем «Заказчик», в лице директора, с одной стороны и Плательщик налога на профессиональный доход {{ $task->user->full_name }} (ИНН {{ $task->user->inn }}), именуемый в дальнейшем «Исполнитель», с другой стороны составили настоящий акт о нижеследующем.
		</p>
		<p>
			Исполнителем выполнены, а Заказчиком принят услуги к Договору №{{ $agreement->number }} от {{ \Carbon\Carbon::parse($agreement->date)->format('d.m.Y') }} г (далее — Договор) на следующие работы.
		</p>




		<table width="100%" border="1" cellspacing="0" cellpadding="2">
			<thead>
				<tr>
					<th>№ п/п</th>
					<th>Название заказанных работ</th>
					<th>Объем</th>
					<th>Цена</th>
					<th>Сумма</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>1</td>
					<td>{{ $task->name }}</td>
					<td>1</td>
					<td>{{ $task->sum }} руб.</td>
					<td>{{ $task->sum }} руб.</td>
				</tr>
				<tr>
					<td colspan="4" align="right">Итого</td>
					<td>{{ $task->sum }} руб.</td>
				</tr>
			</tbody>
		</table>

		<p>
			Качество выполненных работ проверено Заказчиком и соответствует условиям договора
		</p>
		<p>
			Претензии по работе отсутствуют
		</p>
		<p>
			Подписанием настоящего Акта Заказчик подтверждает то обстоятельство, что оплате подлежит вознаграждение Исполнителя в сумме {{ $task->sum }} рублей, НДС не облагается в связи с применением Исполнителем специального налогового режима «Налог на профессиональный доход».
		</p>
	</body>
</html>