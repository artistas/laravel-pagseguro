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
  },
  error: function(response) {
    displayError(translateErrors(first(response['errors'])));
  }
});

function first(obj) {
    for (var a in obj) return a;
}

function getCardBrand(bin) {
  PagSeguroDirectPayment.getBrand({
    cardBin: bin,
    success: function(response) {
      if (brand === undefined || brand['name'] !== response['brand']['name']) {
        brand = response['brand'];
        constructCreditCardForm();
        getInstallments();
      }
    },
    error: function(response) {
      displayError(translateErrors(first(response['errors'])));
    }
  });
}

function getInstallments() {
  PagSeguroDirectPayment.getInstallments({
    amount: amount,
    brand: brand !== undefined ? brand['name'] : null,
    success: function(response) {
      installments = response['installments'][brand['name']];
      constructInstallmentsForm();
    },
    error: function(response) {
      displayError(translateErrors(first(response['errors'])));
    }
  });
}

function getCreditCardToken(cardNumber, cvv, expirationMonth, expirationYear) {
  PagSeguroDirectPayment.createCardToken({
    cardNumber: cardNumber,
    cvv: cvv,
    expirationMonth: expirationMonth,
    expirationYear: expirationYear,
    brand: brand !== undefined ? brand['name'] : null,
    success: function(response) {
      token = response['card']['token'];
      submitCreditCard(true);
    },
    error: function(response) {      
      displayError(translateErrors(first(response['errors'])));
      submitCreditCard();
    }
  });
}

function translateErrors(errorID) {
  switch (errorID) {
    case '10000':
      return 'Cartão de Crédito não reconhecido.'
    default:
      return 'Não foi possivel processar a sua requisição.'
  }
}
</script>
