<script type="text/javascript">
function constructOnlineDebit(availabelOnlineDebit) {
  var html;

  $.each(availabelOnlineDebit, function(index, bank) {
    html = '<label class="btn btn-default">';

    html += '<input type="radio" name="bankName" value="'+bank['name']+'">';
    html += '<img src="https://stc.pagseguro.uol.com.br'+bank['images']['MEDIUM']['path']+'"><br> '+bank['displayName'];

    html += '</label>';

    $('#online_debit').append(html);
  });
}

function constructCreditCard(availableCards) {
  var total = availableCards.length;
  var eachColumn = Math.round(total / 2);
  var html = '';
  var cont = 1;

  $.each(availableCards, function(index, card) {
    if (cont === 1) {
      html += '<div class="col-sm-6">';
      html += '<ul class="list-group">';
    }
    html += '<li class="list-group-item" id="list'+card['name']+'">';
    html += '<img src="https://stc.pagseguro.uol.com.br'+card['images']['SMALL']['path']+'"> '+card['displayName'];
    html += '</li>'

    cont++;
    if (cont > eachColumn || total === index + 1) {
      cont = 1;
      html += '</ul>';
      html += '</div>';
    }
  });

  $('#credit_card').html(html);
}

$('input[name=card_number]').on('keypress blur change', function() {
  if (this.value.length >= 6) {
    var bin = this.value.slice(0, 6);
    getCardBrand(bin);
  }
});

function constructInstallmentsForm() {
  var html;

  $('#installments').empty().append('<option>Escolha a forma de parcelamento</option>');

  $.each(installments, function(index, installment) {
    html = '<option value="'+installment['quantity']+'|'+installment['installmentAmount']+'">';
    html += installment['quantity']+'x de ';
    html += formatReal(installment['installmentAmount']);
    if (installment['interestFree'])
      html += ' sem juros';
    html += '</option>';

    $('#installments').append(html);
  });
}

function constructCreditCardForm() {
  $('.list-group-item.active').removeClass('active');
  $('#list'+brand['name'].toUpperCase()).addClass('active');
  $('#chosenCard').html('<img src="https://stc.pagseguro.uol.com.br'+paymentMethods['CREDIT_CARD']['options'][brand['name'].toUpperCase()]['images']['MEDIUM']['path']+'">');
}

$('#btnParcels').click(function(e) {
  e.preventDefault();
  $('#senderHash').val(PagSeguroDirectPayment.getSenderHash());
  if ($(this).parent().prop('id') == 'presCredit_card') {
        getCreditCardToken();
  }
  else {
        $('#formPayment').submit();
  }
});

function formatReal(mixed) {
    mixed = parseFloat(mixed);
    var int = parseInt(mixed.toFixed(2).toString().replace(/[^\d]+/g, ''));
    var tmp = int + '';
    tmp = tmp.replace(/([0-9]{2})$/g, ",$1");
    if (tmp.length > 6)
        tmp = tmp.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");

    return 'R$ '+tmp;
}

$('#paymentMethods a').on('shown.bs.tab', function (e) {
  if ($(this).parent().prop('id') == 'presCredit_card') {
        $('#paymentMethod').val('creditCard');
  }
  else if ($(this).parent().prop('id') == 'presOnline_debit') {
        $('#paymentMethod').val('eft');
  }
  else {
        $('#paymentMethod').val('boleto');
  }
});
</script>
