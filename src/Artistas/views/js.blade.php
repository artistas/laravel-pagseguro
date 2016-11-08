<script type="text/javascript" src="{{ PagSeguro::getUrl()['javascript'] }}"></script>
<script type="text/javascript">
var paymentMethods;
var brand;
var token;
var installments;

PagSeguroDirectPayment.setSessionId('{{ PagSeguro::getSession() }}');
getPaymentMethods();

function getPaymentMethods() {
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
      var error = first(response['errors']);
      if (error === '59001') {
        resetSession(getPaymentMethods);
      } else {
        displayError(translateErrors(error));
      }
    }
  });
}

function first(obj) {
    for (var a in obj) return a;
}

function resetSession(callback) {
  $.get('/pagseguro/session/reset', function(data) {
    PagSeguroDirectPayment.setSessionId(data);
    callback();
  });
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
      var error = first(response['errors']);
        displayError(translateErrors(error));
    }
  });
}

function getInstallments() {
  if(maxInstallmentNoInterest !== undefined) {
    PagSeguroDirectPayment.getInstallments({
      amount: amount,
      brand: brand !== undefined ? brand['name'] : null,
      maxInstallmentNoInterest: maxInstallmentNoInterest,
      success: function(response) {
        installments = response['installments'][brand['name']];
        constructInstallmentsForm();
      },
      error: function(response) {
        var error = first(response['errors']);
          displayError(translateErrors(error));
      }
    });
  } else {
    PagSeguroDirectPayment.getInstallments({
      amount: amount,
      brand: brand !== undefined ? brand['name'] : null,      
      success: function(response) {
        installments = response['installments'][brand['name']];
        constructInstallmentsForm();
      },
      error: function(response) {
        var error = first(response['errors']);
          displayError(translateErrors(error));
      }
    });
  }
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
      var error = first(response['errors']);
        displayError(translateErrors(error));
        submitCreditCard();
    }
  });
}

function translateErrors(errorID) {
  switch (errorID) {
    case '10000':
      return 'Cartão de Crédito não reconhecido.'
    case '10001':
      return 'O tamanho do Cartão de Crédito é inválido.'
    case '10002':
      return 'Data de Vencimento inválida.'
    case '10003':
      return 'CVV inválido.'
    case '10004':
      return 'CVV é obrigatório.'
    case '10006':
      return 'O tamanho do CVV é inválido.'
    default:
      return 'Não foi possivel processar a sua requisição.'
  }
}
</script>
