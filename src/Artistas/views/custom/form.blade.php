<h2>Escolha a forma de Pagamento:</h2>
<form action="/loja-virtual/gerar-pedido" id="formPayment" method="post">
  <!-- Nav tabs -->
  <ul class="nav nav-tabs nav-justified" id="paymentMethods" role="tablist">
    <li role="presentation" id="presBoleto">
      <a href="#tabBoleto" aria-controls="tabBoleto" role="tab" data-toggle="tab">
        Boleto
      </a>
    </li>
    <li role="presentation" id="presOnline_debit">
      <a href="#tabOnline_debit" aria-controls="tabOnline_debit" role="tab" data-toggle="tab">
        Débito Online
      </a>
    </li>
    <li role="presentation" id="presCredit_card" class="active">
      <a href="#tabCredit_card" aria-controls="tabCredit_card" role="tab" data-toggle="tab">
        Cartão de Crédito
      </a>
    </li>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane" id="tabBoleto">
      <h3>Boleto Bancário</h3>
    </div>
    <div role="tabpanel" class="tab-pane" id="tabOnline_debit">
      <h3>Bancos disponíveis</h3>
      <div class="btn-group" data-toggle="buttons" id="online_debit">
      </div>
    </div>
    <div role="tabpanel" class="tab-pane active" id="tabCredit_card">
      <div class="row">
        <div class="col-sm-5">
          <h3>Cartões disponíveis:</h3>
          <div class="row" id="credit_card">
          </div>
        </div>
        <div class="col-sm-7">
          <h3>Titular do cartão:</h3>
          <div class="row">
            <div class="col-sm-7">
              <div class="form-group">
                <input type="text" placeholder="Nome Completo" class="form-control" name="nome_completo">
              </div>
            </div>
            <div class="col-sm-5">
              <div class="form-group">
                <input type="text" placeholder="CPF" class="form-control" name="cpf">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-5">
              <div class="form-group">
                <input type="text" placeholder="Data de Nascimento" class="form-control" name="nascimento">
              </div>
            </div>
            <div class="col-sm-2">
              <div class="form-group">
                <input type="text" placeholder="DDD" class="form-control" name="ddd">
              </div>
            </div>
            <div class="col-sm-5">
              <div class="form-group">
                <input type="text" placeholder="Telefone" class="form-control" name="telefone">
              </div>
            </div>
          </div>
          <h3>Dados do cartão:</h3>
          <div class="form-group">
            <div class="row">
              <div class="col-sm-10 col-xs-8">
                <input type="text" placeholder="Número do cartão" class="form-control" name="card_number">
              </div>
              <div class="col-sm-2 col-xs-4" id="chosenCard">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-3">
              <div class="form-group">
                <input type="text" placeholder="CVV" class="form-control" name="cvv">
              </div>
            </div>
            <div class="col-sm-2 col-xs-6">
              <div class="form-group">
                <input type="text" placeholder="MM" class="form-control" name="expiration_month">
              </div>
            </div>
            <div class="col-sm-2 col-xs-6">
              <div class="form-group">
                <input type="text" placeholder="AA" class="form-control" name="expiration_year">
              </div>
            </div>
            <div class="col-sm-5">
              <div class="form-group">
                <select class="form-control" id="installments" name="installments">
                  <option>Informe o número do cartão</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <input type="hidden" name="paymentMethod" id="paymentMethod">
  <input type="hidden" name="creditCardToken" id="creditCardToken">
  <input type="hidden" name="senderHash" id="senderHash">

  {{ csrf_field() }}

  <button type="submit" class="btn btn-success pull-right" id="btnParcels">Finalizar Compra</button>
</form>
