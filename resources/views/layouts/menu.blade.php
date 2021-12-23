<div class="navbar-expand-md" id="project_menu" hidden>
	<div class="collapse navbar-collapse" id="navbar-menu">
		<div class="navbar navbar-light">
			<div class="container-xl">
				<ul id="main-menu" class="navbar-nav">
					<li class="nav-item" data-menu="projects">
						<a href="/client/projects" class="nav-link">
							<i class="fad fa-arrow-left me-2"></i>К проектам
						</a>
					</li>
					{{-- <li class="nav-item" data-menu="contractors"> --}}
					{{-- 	<a href="/project/{{ $project_id ?? '' }}/contractors" class="nav-link"> --}}
					{{-- 		<i class="fad fa-user-friends me-2"></i>Исполнители --}}
					{{-- 	</a> --}}
					{{-- </li> --}}
					<li class="nav-item" data-menu="tasks">
						<a href="/project/{{ $project_id ?? '' }}/tasks" class="nav-link">
							<i class="fad fa-tachometer-alt me-2"></i>Задачи
						</a>
					</li>
					<li class="nav-item" data-menu="payouts">
						<a href="/project/{{ $project_id ?? '' }}/payouts" class="nav-link">
							<i class="fad fa-money-check-edit me-2"></i>Платежи
						</a>
					</li>

					{{-- <li class="nav-item" data-menu="documents">
						<a href="/project/{{ $project_id ?? '' }}/documents"class="nav-link">
							<i class="fad fa-file-alt me-2"></i>Документы
						</a>
					</li> --}}
				</ul>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	menu = '';
	$(function(){
		var MenuManager = new MenuManagerClass(menu);
	});

	class MenuManagerClass {
		constructor(menu){
			$('a[data-menu="' + menu + '"]').addClass('active');
		}
	}
</script>
