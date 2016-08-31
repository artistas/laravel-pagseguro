<script type="text/javascript" src="{{ PagSeguro::getUrl()['javascript'] }}"></script>
<script type="text/javascript">
var paymentMethods;
var brand;
var token;
var installments;
$('#outroTitular').hide();

PagSeguroDirectPayment.setSessionId('{{ PagSeguro::getSession() }}');

PagSeguroDirectPayment.getPaymentMethods({
  amount: 500.00,
  success: function(response) {
    if(response['error'] === false) {
      paymentMethods = response['paymentMethods'];
      constructCreditCard();
      constructOnlineDebit();
    }
  },
  error: function(response) {
    //tratamento do erro
  },
  complete: function(response) {
    //tratamento comum para todas chamadas
  }
});

function constructOnlineDebit() {
  var availabelOnlineDebit = [];

  $.each(paymentMethods['ONLINE_DEBIT']['options'], function(index, bank) {
    if (bank['status'] === 'AVAILABLE') {
      availabelOnlineDebit.push(bank);
    }
  });

  var html;

  $.each(availabelOnlineDebit, function(index, bank) {
    html = '<label class="radio-inline">';

    html += '<input type="radio" name="bankName" value="'+bank['name']+'">';
    html += '<img src="https://stc.pagseguro.uol.com.br'+bank['images']['SMALL']['path']+'"> '+bank['displayName'];

    html += '</label>';

    $('#online_debit').append(html);
  });
}

function constructCreditCard() {
  var availableCards = [];

  $.each(paymentMethods['CREDIT_CARD']['options'], function(index, card) {
    if (card['status'] === 'AVAILABLE') {
      availableCards.push(card);
    }
  });

  var total = availableCards.length;
  var columns = 3;
  var columnsClass = 'col-sm-4';
  var rowClass = 'row';
  var eachColumn = Math.round(total / columns);
  var html = '';
  var cont = 1;

  html += '<div class="'+rowClass+'">';

  $.each(availableCards, function(index, card) {
    if (cont === 1) {
      html += '<div class="'+columnsClass+'">';
    }
    html += '<img src="https://stc.pagseguro.uol.com.br'+card['images']['SMALL']['path']+'"> '+card['displayName']+'<br>';
    cont++;
    if (cont > eachColumn || total === index + 1) {
      cont = 1;
      html += '</div>';
    }
  });

  html += '</div>';

  $('#credit_card').html(html);
}

$('input[type=radio][name=titular]').change(function() {
  if (this.value === "nao") {
    $('#outroTitular').show();
  }
  else {
    $('#outroTitular').hide();
  }
});

$('input[name=card_number]').on('keypress blur change', function() {
  if (this.value.length >= 6) {
    var bin = this.value.slice(0, 6);
    getCardBrand(bin);
  }
});

function getCardBrand(bin) {
  PagSeguroDirectPayment.getBrand({
    cardBin: bin,
    success: function(response) {
      if (brand === undefined || brand['name'] !== response['brand']['name']) {
        brand = response['brand'];
        $('#brand').val(brand['name']);
        constructCreditCardForm();
        getInstallments();
      }
    },
    error: function(response) {
      //tratamento do erro
    },
    complete: function(response) {
      //tratamento comum para todas chamadas
    }
  });
}

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

function getInstallments() {
  PagSeguroDirectPayment.getInstallments({
    amount: 500.00,
    brand: brand['name'],
    success: function(response) {
      installments = response['installments'][brand['name']];
      constructInstallmentsForm();
    },
    error: function(response) {
      //tratamento do erro
    },
    complete: function(response) {
      //tratamento comum para todas chamadas
    }
  });
}

function constructCreditCardForm() {
  $('#chosenCard').html('<img src="https://stc.pagseguro.uol.com.br'+paymentMethods['CREDIT_CARD']['options'][brand['name'].toUpperCase()]['images']['MEDIUM']['path']+'">');
}

function getCreditCardToken() {
  PagSeguroDirectPayment.createCardToken({
    cardNumber: $("#cartao").val(),
    cvv: $("#cvv").val(),
    expirationMonth: $("#validadeMes").val(),
    expirationYear: 20+$("#validadeAno").val(),
    brand: brand['name'],
    success: function(response) {
      token = response['card']['token'];
      $('#creditCardToken').val(token);
      $('#formPayment').submit();
    },
    error: function(response) {
      //tratamento do erro
    },
    complete: function(response) {
      //tratamento comum para todas chamadas
    }
  });
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
