<script type="text/javascript" src="{{ PagSeguro::getUrl()['javascript'] }}"></script>
<script type="text/javascript">
var paymentMethods;
var brand;
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

$('input[name=card_number]').keypress(function() {
  if (this.value.length >= 6 && brand === undefined) {
    var bin = this.value.slice(0, 6);
    checkCardBin(bin);
  }
});

function checkCardBin(bin) {
  PagSeguroDirectPayment.getBrand({
    cardBin: bin,
    success: function(response) {
      var brand = response;
    },
    error: function(response) {
      //tratamento do erro
    },
    complete: function(response) {
      //tratamento comum para todas chamadas
    }
  });
}
</script>
