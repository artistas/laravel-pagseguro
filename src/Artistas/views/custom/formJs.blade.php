<script type="text/javascript">
/* Parametros */
var amount=500.00;
var maxInstallmentNoInterest;

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
  $('#brand').val(brand['name']);
  $('.list-group-item.active').removeClass('active');
  $('#list'+brand['name'].toUpperCase()).addClass('active');
  $('#chosenCard').html('<img src="https://stc.pagseguro.uol.com.br'+paymentMethods['CREDIT_CARD']['options'][brand['name'].toUpperCase()]['images']['MEDIUM']['path']+'">');
}

function handleSubmit(e) {
  $('#senderHash').val(PagSeguroDirectPayment.getSenderHash());

  if ($('.nav-tabs .active').prop('id') == 'presCredit_card') {
    e.preventDefault();
    getCreditCardToken(
      $('input[name=card_number]').val(),
      $('input[name=cvv]').val(),
      $('input[name=expiration_month]').val(),
      20+$('input[name=expiration_year]').val()
    );
  } else if ($('.nav-tabs .active').prop('id') == 'presOnline_debit') {
    if ($('input[name=bankName]:checked').val() === undefined) {
      e.preventDefault();
      displayError('Escolha um banco para prosseguir com a opção Débito Online.');
      $('#formPayment').one('submit', function(e) {
        handleSubmit(e);
      });
    }
  }
}

$('#formPayment').one('submit', function(e) {
  handleSubmit(e);
});

function submitCreditCard(submit) {
  if (submit) {
    $('#creditCardToken').val(token);
    $('#formPayment').submit();
  } else {
    $('#formPayment').one('submit', function(e) {
      handleSubmit(e);
    });
  }
}

function formatReal(mixed) {
    mixed = parseFloat(mixed);
    var int = parseInt(mixed.toFixed(2).toString().replace(/[^\d]+/g, ''));
    var tmp = int + '';
    tmp = tmp.replace(/([0-9]{2})$/g, ",$1");
    if (tmp.length > 6)
        tmp = tmp.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");

    return 'R$ '+tmp;
}

function displayError(error) {
  $('#errors').removeClass('hidden').html(error);
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
