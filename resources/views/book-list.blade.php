<!DOCTYPE html>
<html>
<head>
  <title>DataTable AjaX</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <link  href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
  <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
</head>
<body>
<div class="container mt-4">
  <div class="col-md-12 mt-1 mb-2"><button type="button" id="addNewBook" class="btn btn-success">Add</button></div>
  <div class="card">
    <div class="card-header text-center font-weight-bold">
    <div class="float-left">
      <input type="text" class="form-control"   placeholder="Search book" id="search">
    </div>
    </div>
    <div class="card-body">
    <table class="table table-bordered" id="datatable-ajax-crud">
           <thead>
              <tr>
                 <th>Id</th>
                 <th>Book Title</th>
                 <th>Code</th>
                 <th>Author</th>
                 <th>Created at</th>
                 <th>Action</th>
              </tr>
           </thead>
        </table>
    </div>
  </div>
  <!-- boostrap add and edit book model -->
    <div class="modal fade" id="ajax-book-model" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title" id="ajaxBookModel"></h4>
          </div>
          <div class="modal-body">
            <form action="javascript:void(0)" id="addEditBookForm" name="addEditBookForm" class="form-horizontal" method="POST">
              <input type="hidden" name="id" id="id">
              <div class="form-group">
                <label for="name" class="col-sm-2 control-label">Book Name</label>
                <div class="col-sm-12">
                  <input type="text" class="form-control" id="title" name="title" placeholder="Enter Book Name" maxlength="50" required="">
                </div>
              </div>  
              <div class="form-group">
                <label for="name" class="col-sm-2 control-label">Book Code</label>
                <div class="col-sm-12">
                  <input type="text" class="form-control" id="code" name="code" placeholder="Enter Book Code" maxlength="50" required="">
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label">Book Author</label>
                <div class="col-sm-12">
                  <input type="text" class="form-control" id="author" name="author" placeholder="Enter author Name" required="">
                </div>
              </div>
              <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-primary" id="btn-save" value="addNewBook">Save changes
                </button>
              </div>
            </form>
          </div>
          <div class="modal-footer">
          </div>
        </div>
      </div>
    </div>
<!-- end bootstrap model -->
<script type="text/javascript">
 $(document).ready( function () {
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var dt_example = $('#datatable-ajax-crud').DataTable({
           processing: true,
           serverSide: true,
           ajax: "{{ url('ajax-datatable-crud') }}",
           columns: [
                    {data: 'id', name: 'id', 'visible': false},
                    { data: 'title', name: 'title' },
                    { data: 'code', name: 'code' },
                    { data: 'author', name: 'author' },
                    { data: 'created_at', name: 'created_at' },
                    {data: 'action', name: 'action', orderable: true},
                 ],
          order: [[0, 'desc']]
    });
    $('#search').on('keyup', function(){
      dt_example.search( $(this).val() ).draw();
    });
    $('#addNewBook').click(function () {
       $('#addEditBookForm').trigger("reset");
       $('#ajaxBookModel').html("Add Book");
       $('#ajax-book-model').modal('show');
    });
    $('body').on('click', '.edit', function () {
        var id = $(this).data('id');
        // ajax
        $.ajax({
            type:"POST",
            url: "{{ url('edit-book') }}",
            data: { id: id },
            dataType: 'json',
            success: function(res){
              $('#ajaxBookModel').html("Edit Book");
              $('#ajax-book-model').modal('show');
              $('#id').val(res.id);
              $('#title').val(res.title);
              $('#code').val(res.code);
              $('#author').val(res.author);
           }
        });
    });
    $('body').on('click', '.delete', function () {
       if (confirm("Delete Record?") == true) {
        var id = $(this).data('id');
        // ajax
        $.ajax({
            type:"POST",
            url: "{{ url('delete-book') }}",
            data: { id: id },
            dataType: 'json',
            success: function(res){
              var oTable = $('#datatable-ajax-crud').dataTable();
              oTable.fnDraw(false);
           }
        });
       }
    });
    $('body').on('click', '#btn-save', function (event) {
          var id = $("#id").val();
          var title = $("#title").val();
          var code = $("#code").val();
          var author = $("#author").val();
          $("#btn-save").html('Please Wait...');
          $("#btn-save"). attr("disabled", true);
          // ajax
          $.ajax({
            type:"POST",
            url: "{{ url('add-update-book') }}",
            data: {
              id:id,
              title:title,
              code:code,
              author:author,
            },
            dataType: 'json',
            success: function(res){
            $("#ajax-book-model").modal('hide');
            var oTable = $('#datatable-ajax-crud').dataTable();
            oTable.fnDraw(false);
            $("#btn-save").html('Submit');
            $("#btn-save"). attr("disabled", false);
           }
        });

    });
});
</script>
<script>       

search();
function search(){
     var keyword = $('#search').val();
     $.post('{{ route("book.search") }}',
      {
         _token: $('meta[name="csrf-token"]').attr('content'),
         keyword:keyword
       },
       function(data){
        table_post_row(data);
          console.log(data);
       });
}
function table_post_row(res){
let htmlView = '';
if(res.books.length <= 0){
    htmlView+= `
       <tr>
          <td colspan="4">No data.</td>
      </tr>`;
}
for(let i = 0; i < res.books.length; i++){
    htmlView += `
        <tr>
           <td>`+ (i+1) +`</td>
              <td>`+res.books[i].code+`</td>
              <td>`+res.books[i].author+`</td>
              <td>`+res.books[i].created_at+`</td>
        </tr>`;
}
     $('tbody').html(htmlView);
}
</script>
</div>  
</body>
</html>