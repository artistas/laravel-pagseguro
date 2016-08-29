<script type="text/javascript" src="{{ PagSeguro::getUrl()['javascript'] }}"></script>
<script type="text/javascript">
var paymentMethods;
var brand;
var token;
var installments;
$('#outroTitular').hide();

PagSeguroDirectPayment.setSessionId('{{ PagSeguro::getSessionId() }}');

PagSeguroDirectPayment.getPaymentMethods({
  amount: 500.00,
  success: function(response) {
    if(response['error'] === false) {
      paymentMethods = response['paymentMethods'];
      constructCreditCard();
    }
  },
  error: function(response) {
    //tratamento do erro
  },
  complete: function(response) {
    //tratamento comum para todas chamadas
  }
});

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
      brand = response;
      constructCreditCardForm();
      getInstallments();
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

  $('#installments').empty().append('<option>Escolha uma forma de parcelamento</option>');

  $.each(installments, function(index, installment) {
    html = '<option>';
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
    brand: brand['brand']['name'],
    success: function(response) {
      installments = response['installments'][brand['brand']['name']];
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
  $('#chosenCard').html('<img src="https://stc.pagseguro.uol.com.br'+paymentMethods['CREDIT_CARD']['options'][brand['brand']['name'].toUpperCase()]['images']['MEDIUM']['path']+'">');
}

function getCreditCardToken() {
  PagSeguroDirectPayment.createCardToken({
    cardNumber: $("#cartao").val(),
    cvv: $("#cvv").val(),
    expirationMonth: $("#validadeMes").val(),
    expirationYear: $("#validadeAno").val(),
    brand: brand['brand']['name'],
    success: function(response) {
      token = response['card']['token'];
    },
    error: function(response) {
      //tratamento do erro
    },
    complete: function(response) {
      //tratamento comum para todas chamadas
    }
  });
}

$('#btnParcels').click(function() {
  getCreditCardToken();
});

function formatReal(mixed) {
    if(mixed == 0) {
        return 'GRÁTIS';
    }

    mixed = parseFloat(mixed);
    var int = parseInt(mixed.toFixed(2).toString().replace(/[^\d]+/g, ''));
    var tmp = int + '';
    tmp = tmp.replace(/([0-9]{2})$/g, ",$1");
    if (tmp.length > 6)
        tmp = tmp.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");

    return 'R$ '+tmp;
}
</script>
