@extends('layouts.master')

@section('title')
	Документы
@stop

@section('styles')
<style type="text/css">
tr.active {
    background-color: #83b7f4 !important;
    color: white !important;
}

.dts_label {
	display: none;
}
</style>
@stop

@section('scripts')
	<script type="text/javascript" src="/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="/js/dataTables.scroller.min.js"></script>
@stop

@section('content')
<div class="page-wrapper">
	<div class="container-xl">
		<!-- Page title -->
		<div class="page-header d-print-none mt-4">
			<div class="row align-items-center">
				<div class="col">
					<!-- Page pre-title -->
					<div id="project_name" class="page-pretitle"></div>
					<h2 class="page-title">
						<b class="fal fa-file me-2"></b> Документы
					</h2>
				</div>
				<!-- Page title actions -->
				<div class="col-auto ms-auto d-print-none d-flex">
					<div class="btn-list">
						<span class="d-none d-sm-inline">
							<button id="download_selected_btn1" class="btn btn-white" hidden>
								<b class="fa fa-download text-success me-2"></b>Скачать выбранные
							</button>
							<a id="download_templates_btn" href="/files/document_templates.zip" target="_blank" class="btn btn-white">
								<b class="fa fa-download text-success me-2"></b>Скачать шаблоны
							</a>
							<button id="add_document_btn" class="btn btn-white" hidden>
								<b class="fa fa-plus-circle text-success me-2"></b>Добавить документ
							</button>
							<button id="toggle_filter" class="btn btn-white">
								<b class="fad fa-filter"></b>&nbsp;
							</button>
						</span>
					</div>
				</div>
			</div>
		</div>

		<div id="filter_wrapper" class="mt-3 p-4 rounded border bg-white" hidden>
			<div class="row">
				<div id="project_id_wrapper" class="col-3 form-group" hidden>
					<label class="form-label">Проекты</label>
					<select id="filter_project_id" class="form-select">
						<option value="">Все проекты</option>
					</select>
				</div>
				<div class="col-3 form-group">
					<label class="form-label">Исполнитель</label>
					<input id="contractor" type="text" class="form-control" placeholder="Фамилия или ИНН">
				</div>
				<div class="col-3 form-group">
					<label class="form-label">Дата с</label>
					<input id="date_from" type="text" class="form-control" placeholder="дд.мм.гггг">
				</div>
				<div class="col-3 form-group">
					<label class="form-label">Дата до</label>
					<input id="date_till" type="text" class="form-control" placeholder="дд.мм.гггг">
				</div>
{{-- 				<div id="order_id_wrapper" class="col-3 form-group" hidden>
					<label class="form-label">Ведомость</label>
					<select id="filter_order_id" class="form-select">
						<option value="">Все ведомости</option>
					</select>
				</div> --}}

				<div class="form-selectgroup form-selectgroup-pills d-flex justify-content-start mt-3">
					<label class="form-selectgroup-item">
						<input type="radio" name="document_type" value="" class="form-selectgroup-input" checked>
						<span class="form-selectgroup-label w-100">Все документы</span>
					</label>
					<label class="form-selectgroup-item">
						<input type="radio" name="document_type" value="contract" class="form-selectgroup-input">
						<span class="form-selectgroup-label w-100">Договоры</span>
					</label>
					<label class="form-selectgroup-item">
						<input type="radio" name="document_type" value="act" class="form-selectgroup-input">
						<span class="form-selectgroup-label w-100">Акты</span>
					</label>
					<label class="form-selectgroup-item">
						<input type="radio" name="document_type" value="reciept" class="form-selectgroup-input">
						<span class="form-selectgroup-label w-100">Чеки</span>
					</label>
					<label class="form-selectgroup-item">
						<input type="radio" name="document_type" value="work_order" class="form-selectgroup-input">
						<span class="form-selectgroup-label w-100">Заказ наряды</span>
					</label>
					<label class="form-selectgroup-item">
						<input type="radio" name="document_type" value="other" class="form-selectgroup-input">
						<span class="form-selectgroup-label w-100">Другое</span>
					</label>
                </div>

			</div>
		</div>


		<div class="card mt-3">
			<div class="card-body p-0 pb-3">
				<table id="documents_table" class="table" style="width:100%"></table>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(function(){

		let project_id = $('#project_id').val();

		window.InterfaceManager = new InterfaceManagerClass();
		if (project_id){
 			window.InterfaceManager.menuShow('project_menu');
			window.InterfaceManager.menuActive('documents');
		} else {
 			window.InterfaceManager.menuShow('main_menu');

		}

		window.InterfaceManager.checkAuth();
		window.InterfaceManager.loadMe();
		window.InterfaceManager.notificationsCount();
		//window.InterfaceManager.signUnrequestedCount();


		let DocumentsManager = new DocumentsManagerClass;
		if (project_id) {
			DocumentsManager.project_id = $('#project_id').val();
		}
		DocumentsManager.createInterface();

		if (project_id) {
			let ProjectManager = new ProjectManagerClass;
			ProjectManager.project_id = $('#project_id').val();
			ProjectManager.loadProjectData();
		}
	});


	class DocumentsManagerClass {

		constructor(){
			let ths = this;
		}


		createInterface(){
			let ths = this;
			ths.initDocumentsDatatable();
			ths.initSearchPanel();

			if (ths.project_id) {
				ths.bindAddNewDocumentBtn();
			}
		}

		/**
		 * Initialization of datatable
		 */
		initDocumentsDatatable(){
			let ths = this;
			let csrf_token = $('meta[name="csrf-token"]').attr('content');

			$.fn.dataTable.ext.classes.sPageButton = "btn btn-outline-primary ";
			$.fn.dataTable.ext.classes.sPageButtonActive = "bg-primary text-light ";
			$.fn.dataTable.ext.classes.sProcessing = "text-center mb-3 mx-auto py-3 bg-dark text-light fixed-bottom col-4 rounded";
			$.fn.dataTable.ext.classes.sInfo = "text-center my-2 mx-auto p-2";
			$.fn.dataTable.ext.classes.sRowEmpty = "d-none";
			$.fn.dataTable.ext.classes.sWrapper = "";

			if (ths.project_id) {
				var datatable_url = '{{ env('API_URL') }}/api/project/' + ths.project_id + '/documents/datatable';
				var show_project_name_column = false;
			} else {
				var datatable_url = '{{ env('API_URL') }}/api/documents/datatable';
				var show_project_name_column = true;
			}

			var settings = {
				ajax : {
					url: datatable_url,
					dataSrc: 'data',
					type: 'GET',
					data: function ( d ) {
						d.filter = {};

						let contractor = $('#contractor').val();
						if (contractor != '') {
							d.filter.contractor = contractor;
						}

						let date_from = $('#date_from').val();
						if (date_from != '') {
							d.filter.date_from = date_from;
						}

						let date_till = $('#date_till').val();
						if (date_till != '') {
							d.filter.date_till = date_till;
						}

/*						let order_id = $('#filter_order_id').val();
						if (order_id != '') {
							d.filter.order_id = order_id;
						}*/

						let project_id = $('#filter_project_id').val();
						if (project_id != '') {
							d.project_id = project_id;
						}

						let document_type = $('input[name="document_type"]:checked').val();
						if(document_type != '') {
							d.filter.document_type = document_type;
						}

					},
					xhrFields: {
        			    withCredentials: true
        			}
				},
				processing: true,
				pageLength: 50,
				dom : '<"p-0 overflow-auto"rt><"text-center"<"mt-2"i><"mt-2"p>>',
				sPageButton: "btn btn-dark",
				pagingType: "numbers",
				serverSide: true,
				stateSave: false,
				responsive: false,
				deferRender: true,
				oLanguage: {
					sInfo: "<b>_START_</b> &rarr; <b>_END_</b>, из <b>_TOTAL_</b>",
					sInfoEmpty: "Нет записей для отображения",
					sInfoFiltered: "(отфильтровано из _MAX_)",
					sLoadingRecords: "Загрузка...",
					sProcessing: "<i class='fad fa-spinner fa-pulse'></i> Загрузка...",
					sEmptyTable: "Нет данных в таблице",
				},

				columns: [


					{name: 'document_id', data: 'document_id', title: 'ID', class:'', sortable: true, searchable: true, visible: true},
					{name: 'project_name', data: 'project_name', title: 'Проект', class:'', sortable: true, searchable: true, visible: show_project_name_column,
					render: function (data, type, row, meta ) {
						return '<small><b class="fad fa-folder me-2"></b>' + row.project_name + '</small>';
					}},
					{name: 'document_name', data: 'document_name', title: 'Документ', class:'', sortable: false, searchable: true, visible: true,
					render: function (data, type, row, meta ) {
						return '<small class="font-weight-bold">' + row.document_name + '</small>';
					}},
					{name: 'contractor_inn', data: 'contractor_inn', title: 'ИНН', class:'', sortable: true, searchable: true, visible: false},
					{name: 'contractor_name', data: 'contractor_name', title: 'Исполнитель', class:'', sortable: true, searchable: true, visible: true,
					render: function (data, type, row, meta ) {
						return '<div>' + row.contractor_name + '</div>' + '<small class="d-block">ИНН ' + row.contractor_inn + '</small>';
					}},
					{name: 'order_name', data: 'order_name', title: 'Ведомость', class:'', sortable: true, searchable: true, visible: false},

					{name: 'document_type', data: 'document_type', title: 'Документ', class:'', sortable: true, searchable: true, visible: false,
					render: function (data, type, row, meta ) {
						let document_types = {
							contract: 'Договор',
							work_order: 'Заказ-наряд',
							act: 'Акт',
							reciept: 'Чек',
							other: 'Другое'
						};
						return '<small>' + document_types[row.document_type] + '</small>';
					}},
					{name: 'document_date', data: 'document_date', title: 'Дата', class:'text-center', sortable: false, searchable: true, visible: false},


					{name: 'company_sign_requested', data: 'company_sign_requested', title: 'Отправлено клиенту на подпись', class:'', sortable: false, searchable: false, visible: false},
					{name: 'user_sign_requested', data: 'user_sign_requested', title: 'тправлено исполнителю на подпись', class:'', sortable: false, searchable: false, visible: false},

					{name: 'is_signed_by_company', data: 'is_signed_by_company', title: 'Подписано клиентом', class:'', sortable: false, searchable: false, visible: false},
					{name: 'is_signed_by_user', data: 'is_signed_by_user', title: 'Подписано исполнителем', class:'', sortable: false, searchable: false, visible: false},

					{name: 'sign_status', title: 'Электронная подпись', class:'text-left', sortable: true, searchable: true, visible: true,
					render: function (data, type, row, meta ) {

						if (row.document_type == 'reciept') {
							return "-";
						}


						let b = '';

						b+= '<small class="d-block">';
						if (row.is_signed_by_company == true) {
							b+= '<b class="fad fa-check-circle text-success me-2"></b>';
							b+= 'Клиент: подписано';
						} else if (row.company_sign_requested == true) {
							b+= '<b class="fad fa-hourglass-half text-info me-2"></b>';
							b+= 'Клиент: запрошено, ожидание';
						} else {
							b+= '<b class="fad fa-times-circle text-danger me-2"></b>';
							b+= 'Клиент: не запрошено';
						}
						b+= '</small>';


						b+= '<small class="d-block">';
						if (row.is_signed_by_user == true) {
							b+= '<b class="fad fa-check-circle text-success me-2"></b>';
							b+= 'Исполнитель: подписано';
						} else if (row.user_sign_requested == true) {
							b+= '<b class="fad fa-hourglass-half text-info me-2"></b>';
							b+= 'Исполнитель: запрошено, ожидание';
						} else {
							b+= '<b class="fad fa-times-circle text-danger me-2"></b>';
							b+= 'Исполнитель: не запрошено';
						}
						b+= '</small>';

						return b;
					}},
					{name: 'document_link', data: 'document_link', title: 'Скачать', class:'text-center', sortable: true, searchable: true, visible: true,
					render: function (data, type, row, meta ) {
						return '<a href="' + row.document_link + '" target="_blank" class="btn btn-md btn-white btn-sm btn-more" title="Скачать zip архив с документом и электронными подписями"><b class="fad fa-file-archive me-2"></b> Скачать</a>';
					}},
					{ data: 'document_id', title: 'Выбрать', class:'text-center', sortable: true, searchable: true, visible: false,
					render: function (data, type, row, meta ) {
						return '<b class="check fal fa-square"></b>';
					}}
				],
				rowCallback: function(row, data, index){
					$(row).find('b.check').bind('click', function(){
						if ($(this).hasClass('fa-square')) {
							$(this).removeClass('fa-square')
							$(this).addClass('fa-check-square')
							$('#download_selected_btn').prop('hidden', false);
						} else {
							$(this).addClass('fa-square')
							$(this).removeClass('fa-check-square')
						}
					});
				},
				drawCallback: function(settings){
					$('.dataTables_paginate').find('span').addClass('btn-group');
				}
			}
			ths.documents_datatable = $('#documents_table').DataTable(settings);
		}

		/**
		 * Binding search_input keyup for searching
		 */
		initSearchPanel(){
			let ths = this;

			$('#toggle_filter').bind('click', function(){
				if ($('#filter_wrapper').prop('hidden')) {
					$('#filter_wrapper').prop('hidden', false);
				} else {
					$('#filter_wrapper').prop('hidden', true);
				}
			});

			$('#date_from').mask('99.99.9999', {placeholder:'дд.мм.гггг'});
			$('#date_till').mask('99.99.9999', {placeholder:'дд.мм.гггг'});

			$('input[name="document_type"]').bind('change', function(){
				ths.documents_datatable.ajax.reload();
			});

			$('#contractor, #date_from, #date_till, input[name="document_type"], #filter_project_id').bind('change', function(){
				ths.documents_datatable.ajax.reload();
			});

			if (ths.project_id) {

			} else {
				ths.loadProjects();
				$('#project_id_wrapper').prop('hidden', false);
			}
		}


		/**
		 * Загрузка проектов
		 */
		loadProjects(){
			let ths = this;
			$("#filter_project_id").prop('disabled', true);
			$("#filter_project_id").html("<option value=''>Все проекты</option>");

			var ax = axios.get('{{ env('API_URL') }}/api/projects');
			ax.then(function (response) {
				if (response.data.projects) {
					$.each(response.data.projects, function(i, project){
						$("#filter_project_id").append("<option value='" + project.id + "'>" + project.name + "</option>");
					});
				}
			})
			.catch(function (error) {
				console.log(error);
				bootbox.dialog({
					title: error.response.data.title ?? 'Ошибка',
					message: error.response.data.message ?? error.response.statusText,
					closeButton: false,
					buttons:{
						cancel:{
							label: 'Закрыть',
							className: 'btn-dark'
						}
					}
				});
			})
			.finally(function(){
				$("#filter_project_id").prop('disabled', false);
			});
		}


		/**
		 * Загрузка заказов (ведомостей)
		 */
/*		loadOrders(){
			let ths = this;
			$("#filter_order_id").prop('disabled', true);
			$("#filter_order_id").html("<option value=''>Все ведомости</option>");

			var ax = axios.get('{{ env('API_URL') }}/api/project/' + ths.project_id + '/orders');
			ax.then(function (response) {
				if (response.data.orders) {
					$.each(response.data.orders, function(i, order){
						$("#filter_order_id").append("<option value='" + order.id + "'>" + order.name + "</option>");
					});
				}
			})
			.catch(function (error) {
				console.log(error);
				bootbox.dialog({
					title: error.response.data.title ?? 'Ошибка',
					message: error.response.data.message ?? error.response.statusText,
					closeButton: false,
					buttons:{
						cancel:{
							label: 'Закрыть',
							className: 'btn-dark'
						}
					}
				});
			})
			.finally(function(){
				$("#filter_order_id").prop('disabled', false);
			});
		}*/




		bindFilterProjectContractorsDatatableBtn(){
			let ths = this;
			$('#filter_btn').unbind('click').bind('click', function(){
				ths.project_contractors_datatable.ajax.reload(null, false);
			});

			$('#job_category_filter, #inn_filter, #lastname_filter').unbind('change').bind('change', function(){
				ths.project_contractors_datatable.ajax.reload(null, false);
			});
		}


		bindAddNewDocumentBtn(){
			let ths = this;

			$('#add_document_btn').prop('hidden', false);


			ths.hideSelectContractorBlock();
			ths.hideUploadDocumentBlock();
			ths.hideDocumentPreviewBlock();

			$('#add_document_btn').bind('click', function(){
				$('#add_document_modal').modal('show');
				ths.showSelectContractorBlock()
			});

			$('#add_document_modal').bind('hidden.bs.modal', function(){
				ths.hideSelectContractorBlock();
				ths.hideUploadDocumentBlock();
				ths.hideDocumentPreviewBlock();
			});
		}


		showSelectContractorBlock(){
			let ths = this;
			ths.initProjectContractorsDatatable();
			ths.loadCategories();
			ths.bindFilterProjectContractorsDatatableBtn();
			$('#select_contractor_block').prop('hidden', false);
		}


		hideSelectContractorBlock(){
			let ths = this;
			ths.destroyProjectContractorsDatatable();
			$('#select_contractor_block').prop('hidden', true);
		}


		showUploadDocumentBlock(user_id){
			let ths = this;
				ths.document_user_id = user_id;

			$('#upload_document_block').prop('hidden', false);

			$('#upload_document_file').unbind('change').bind('change', function(e) {
				ths.uploadDocument(e.target.files[0]);
			});

			$('#upload_document_btn').unbind('click').bind('click', function() {
				$('#upload_document_file').trigger('click');
			});

			$('#back_to_select_contractor_btn').unbind('click').bind('click', function() {
				ths.showSelectContractorBlock();
				ths.hideUploadDocumentBlock();
			})
		}

		hideUploadDocumentBlock(){
			let ths = this;
			$('#upload_document_block').prop('hidden', true);
		}


		showDocumentPreviewBlock(){
			let ths = this;
			$('#preview_document_block').prop('hidden', false);
			$('#document_preview_iframe').attr('src', ths.new_document_preview);

			$('#back_to_upload_document_btn').unbind('click').bind('click', function(){
				ths.hideDocumentPreviewBlock();
				ths.showUploadDocumentBlock();
			});

			$('#save_document_btn').unbind('click').bind('click', function(){
				ths.saveDocument();
			});

			$('#new_document_date').mask('99.99.9999', {placeholder:'дд.мм.гггг'});
		}


		hideDocumentPreviewBlock(){
			let ths = this;
			$('#preview_document_block').prop('hidden', true);
			$('#document_preview_iframe').attr('src', '');
		}


		/**
		 * Initialization of datatable
		 */
		initProjectContractorsDatatable(){
			let ths = this;
			let csrf_token = $('meta[name="csrf-token"]').attr('content');

			$.fn.dataTable.ext.classes.sPageButton = "btn btn-outline-primary ";
			$.fn.dataTable.ext.classes.sPageButtonActive = "bg-primary text-light ";
			$.fn.dataTable.ext.classes.sProcessing = "text-center mb-3 mx-auto py-3 bg-dark text-light fixed-bottom  rounded";
			$.fn.dataTable.ext.classes.sInfo = "text-center my-2 mx-auto p-2";
			$.fn.dataTable.ext.classes.sRowEmpty = "d-none";
			$.fn.dataTable.ext.classes.sWrapper = "";

			var settings = {
				ajax : {
					url: '{{ env('API_URL') }}/api/project/' + ths.project_id + '/contractors/datatable',
					dataSrc: 'data',
					type: 'GET',
					data: function ( d ) {
						d.filter = {};
						let job_category_filter = $('#job_category_filter').val();
						if (job_category_filter) {
							d.filter.job_category_id = job_category_filter;
						}

						let inn_filter = $('#inn_filter').val();
						if (inn_filter) {
							d.filter.inn = inn_filter;
						}

						let lastname_filter = $('#lastname_filter').val();
						if (lastname_filter) {
							d.filter.lastname = lastname_filter;
						}
					},
					xhrFields: {
        			    withCredentials: true
        			}
				},
				processing: true,
				pageLength: 50,
				dom : '<"p-0 overflow-auto"rt><"text-center"<"mt-2"i><"mt-2"p>>',
				sPageButton: "btn btn-dark",
				pagingType: "numbers",
				serverSide: true,
				stateSave: false,
				responsive: false,
				deferRender: true,
				destroy: true,
				paging: true,
				scrollY: 200,
				scrollCollapse: false,
				processing: false,
				scroller: {
					rowHeight: 36,
					serverWait: 100,
					boundaryScale: 0.7
				},
				oLanguage: {
					sInfo: "<b>_START_</b> &rarr; <b>_END_</b>, из <b>_TOTAL_</b>",
					sInfoEmpty: "Нет записей для отображения",
					sInfoFiltered: "(отфильтровано из _MAX_)",
					sLoadingRecords: "Загрузка...",
					sProcessing: "<i class='fad fa-spinner fa-pulse'></i> Загрузка...",
					sEmptyTable: "Нет данных в таблице",
				},
				columns: [
					{name: 'name', data: 'name', title: 'ФИО', class:'', sortable: true, searchable: true, visible: true},
					{name: 'inn', data: 'inn', title: 'ИНН', class:'', sortable: true, searchable: true, visible: true},
					{name: 'job_category_name', data: 'job_category_name', title: 'Категория работ', class:'', sortable: true, searchable: true, visible: true},
					{name: 'created_date', data: 'created_date', title: 'Дата регистрации', class:'', sortable: true, searchable: true, visible: true},
					{name: 'id', data: 'id', title: 'Выбрать', class:'', sortable: true, searchable: true, visible: true,
					render: function (data, type, row, meta ) {
						return '<button class="btn btn-sm btn-primary btn-select">Выбрать</button>';
					}},
				],
				rowCallback: function(row, data, index){
					$(row).find('button.btn-select').bind('click', function(){
						ths.showUploadDocumentBlock(data.id);
						ths.hideSelectContractorBlock();
					});
				},
				drawCallback: function(settings){
					$('.dataTables_paginate').find('span').addClass('btn-group');
				}
			}
			ths.project_contractors_datatable = $('#project_contractors_datatable').DataTable(settings);
		}


		destroyProjectContractorsDatatable(){
			let ths = this;
			if (ths.project_contractors_datatable) {
				try{
					ths.project_contractors_datatable.destroy();
					delete(ths.project_contractors_datatable);
				} catch(e){}
			}
		}


		uploadDocument(file){
			let ths = this;

			$('#upload_document_btn').prop('disabled', true);
			$('#upload_document_btn .text').prop('hidden', true);
			$('#upload_document_btn .wait').prop('hidden', false);

			var formData = new FormData();
				formData.append('file', file);

			var ax = axios.post('{{ env('API_URL') }}/api/document/upload', formData);
			ax.then(function (response) {
				if (response.data.message) {
					boottoast.success({
						message: response.data.message,
						title: response.data.title ?? 'Успешно',
						imageSrc: "/images/logo-sm.svg"
					});
				}
				if (response.data.preview) {
					ths.new_document_path = response.data.path;
					ths.new_document_preview = response.data.preview;
					ths.showDocumentPreviewBlock();
					ths.hideUploadDocumentBlock();
				}
			})
			.catch(function (error) {
				console.log(error);
				bootbox.dialog({
					title: error.response.data.title ?? 'Ошибка',
					message: error.response.data.message ?? error.response.statusText,
					closeButton: false,
					buttons:{
						cancel:{
							label: 'Закрыть',
							className: 'btn-dark'
						}
					}
				});
			})
			.finally(function(){

				$('#upload_document_btn').prop('disabled', false);
				$('#upload_document_btn .text').prop('hidden', false);
				$('#upload_document_btn .wait').prop('hidden', true);

				$('#upload_document_file').val("");
			});
		}


		saveDocument(){

			let ths = this;

			let new_document_type = $('#new_document_type').val();
			let new_document_number = $('#new_document_number').val();
			let new_document_date = $('#new_document_date').val();
			let new_document_path = ths.new_document_path;
			let new_document_user_id = ths.document_user_id;

			if (new_document_type == '' || new_document_number == '' || new_document_date == '' || new_document_path == '' || new_document_user_id == '') {
				bootbox.dialog({
					message: 'Необходимо заполнить все поля',
					title:'Ошибка',
					closeButton: false,
					buttons:{
						cancel:{
							label: 'Закрыть',
							className: 'btn-dark'
						}
					}
				});
				return false;
			}

			$('#save_document_btn').prop('disabled', true);
			$('#save_document_btn .text').prop('hidden', true);
			$('#save_document_btn .wait').prop('hidden', false);

			let data = {}
				data.document_type = new_document_type;
				data.document_number = new_document_number;
				data.document_date = new_document_date;
				data.document_path = new_document_path;
				data.document_user_id = new_document_user_id;

			var ax = axios.post('{{ env('API_URL') }}/api/project/' + ths.project_id + '/document', data);
			ax.then(function (response) {
				if (response.data.message) {
					boottoast.success({
						message: response.data.message,
						title: response.data.title ?? 'Успешно',
						imageSrc: "/images/logo-sm.svg"
					});

					$('#add_document_modal').modal('hide');
					ths.documents_datatable.ajax.reload();
				}
			})
			.catch(function (error) {
				console.log(error);
				bootbox.dialog({
					title: error.response.data.title ?? 'Ошибка',
					message: error.response.data.message ?? error.response.statusText,
					closeButton: false,
					buttons:{
						cancel:{
							label: 'Закрыть',
							className: 'btn-dark'
						}
					}
				});
			})
			.finally(function(){
				$('#save_document_btn').prop('disabled', false);
				$('#save_document_btn .text').prop('hidden', false);
				$('#save_document_btn .wait').prop('hidden', true);
			});


		}


		/**
		 * Загрузка списка банков
		 */
		loadCategories(){
			var ax = axios.get('{{ env('API_URL') }}/api/job_categories');
			ax.then(function (response) {
				if (response.data.job_categories) {
					$('#job_category_filter').html('<option value="">Любая</option>')
					$.each(response.data.job_categories, function(i, job_category){
						if (job_category.parent_id == null) {
							$('#job_category_filter').append('<optgroup class="job_category_' + job_category.id + '" label="' + job_category.name + '"></optgroup>');
						}
					});
					$.each(response.data.job_categories, function(i, job_category){
						if (job_category.parent_id != null) {
							$('#job_category_filter .job_category_' + job_category.parent_id).append('<option value="' + job_category.id + '">' + job_category.name + '</option>');
						}
					});
				}
			})
			.catch(function (error) {
				console.log(error);
				bootbox.dialog({
					title: error.response.data.title ?? 'Ошибка',
					message: error.response.data.message ?? error.response.statusText,
					closeButton: false,
					buttons:{
						cancel:{
							label: 'Закрыть',
							className: 'btn-dark'
						}
					}
				});
			})
			.finally(function(){
			});
		}


	}


</script>


@include('add-document')

@stop
