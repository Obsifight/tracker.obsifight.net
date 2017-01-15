/*
ObsiBans - Eywek
*/

var DataTableElements = [];

// -- .DataTable --
$(function () {
  $('table.dataTable').each(function(e) {

    if(!$.fn.dataTable.isDataTable(this)) {
      var name = $(this).attr('id');
      DataTableElements[name] = $(this).DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": false,
        "ordering": false,
        "info": false,
        "autoWidth": false,
        'searching': true,
        "language" : {
            "sProcessing":     "Traitement en cours...",
            "sSearch":         "Rechercher&nbsp;:",
            "sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
            "sInfo":           "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
            "sInfoEmpty":      "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
            "sInfoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
            "sInfoPostFix":    "",
            "sLoadingRecords": "Chargement en cours...",
            "sZeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
            "sEmptyTable":     "Aucune donn&eacute;e disponible dans le tableau",
            "oPaginate": {
                "sFirst":      "Premier",
                "sPrevious":   "Pr&eacute;c&eacute;dent",
                "sNext":       "Suivant",
                "sLast":       "Dernier"
            },
            "oAria": {
                "sSortAscending":  ": activer pour trier la colonne par ordre croissant",
                "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
            }
        }
      });
    }

  });
});


$(".refreshDataTable").on("click", function (event) {

  event.preventDefault();

  var button = $(this);
  var el = $(button.attr('data-table'));
  var url = button.attr('data-url');
  var start = parseInt(button.attr('data-end'));
  var button_content = button.html();

  button.attr('disabled', true).addClass('disabled').html('Chargement...');

  var interval = 50;

  $.ajax({
    url: url,
    type: "POST",
    data: {
      start: start,
      end : interval
    }
  }).done(function (result) {

    if(result.length > 0) {
      var name = el.attr('id');
      for (var key in result) {
        DataTableElements[name].row.add(result[key]).draw();
      }
      button.attr('data-end', (start+interval));

      //on change la page
      var page = (start+10) / 10;
      DataTableElements[name].page(page).draw('page');

    } else {
      button.remove();
      alert('Toutes les données sont déjà chargées !');
    }

    button.attr('disabled', false).removeClass('disabled').html(button_content);

  }).fail(function (jqXHR, textStatus, errorThrown) {

    console.log(textStatus, errorThrown);
    alert('Erreur lors du chargement des données !');

    button.attr('disabled', false).removeClass('disabled').html(button_content);

  });
});

$('#search').on('submit', function(e) {
  e.preventDefault();
  var form = $(this);
  var input = form.find('input');

  window.location='/see/'+input.val();
});
