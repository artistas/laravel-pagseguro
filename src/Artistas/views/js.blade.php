<script type="text/javascript" src="{{ PagSeguro::getUrl()['javascript'] }}"></script>
<script type="text/javascript">
var paymentMethods;
var brand;
var token;
var installments;

PagSeguroDirectPayment.setSessionId('{{ PagSeguro::getSession() }}');

PagSeguroDirectPayment.getPaymentMethods({
  amount: amount,
  success: function(response) {
    if(response['error'] === false) {
      paymentMethods = response['paymentMethods'];

      var availabelOnlineDebit = [];
      var availableCards = [];

      $.each(paymentMethods['ONLINE_DEBIT']['options'], function(index, bank) {
        if (bank['status'] === 'AVAILABLE') {
          availabelOnlineDebit.push(bank);
        }
      });

      $.each(paymentMethods['CREDIT_CARD']['options'], function(index, card) {
        if (card['status'] === 'AVAILABLE') {
          availableCards.push(card);
        }
      });

      constructOnlineDebit(availabelOnlineDebit);
      constructCreditCard(availableCards);
    }
  },
  error: function(response) {
    //tratamento do erro
  },
  complete: function(response) {
    //tratamento comum para todas chamadas
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

function getInstallments() {
  PagSeguroDirectPayment.getInstallments({
    amount: amount,
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
</script>
