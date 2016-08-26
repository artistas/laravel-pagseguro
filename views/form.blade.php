<h2>Escolha a forma de Pagamento:</h2>
<!-- Nav tabs -->
<ul class="nav nav-tabs nav-justified" id="paymentMethods" role="tablist">
  <li role="presentation" id="presBoleto"><a href="#tabBoleto" aria-controls="tabBoleto" role="tab" data-toggle="tab">Boleto</a></li>
  <li role="presentation" id="presOnline_debit"><a href="#tabOnline_debit" aria-controls="tabOnline_debit" role="tab" data-toggle="tab">Débito Online</a></li>
  <li role="presentation" id="presCredit_card"><a href="#tabCredit_card" aria-controls="tabCredit_card" role="tab" data-toggle="tab">Cartão de Crédito</a></li>
</ul>

<!-- Tab panes -->
<div class="tab-content">
  <div role="tabpanel" class="tab-pane active" id="tabBoleto">
    Boleto
  </div>
  <div role="tabpanel" class="tab-pane" id="tabOnline_debit">
    Debito
  </div>
  <div role="tabpanel" class="tab-pane" id="tabCredit_card">
    <h3>Cartões disponíveis:</h3>

    <div id="credit_card"></div>

    <div class="row">
      <div class="col-sm-6">
        <h3>Titular do cartão:</h3>

        <label class="radio-inline">
          <input type="radio" name="titular" value="titular" checked> Sou o titular do cartão
        </label>

        <label class="radio-inline">
          <input type="radio" name="titular" value="nao"> Outro titular
        </label>

        <div id="outroTitular">
          {!! Form::text('nome_completo', null, ['class' => 'form-control', 'placeholder' => 'Nome Completo']) !!}
          {!! Form::text('cpf', null, ['class' => 'form-control', 'placeholder' => 'CPF']) !!}
          <div class="row">
            <div class="col-sm-5">
              {!! Form::text('nascimento', null, ['class' => 'form-control', 'placeholder' => 'Data de Nascimento']) !!}
            </div>
            <div class="col-sm-2">
              {!! Form::text('ddd', null, ['class' => 'form-control', 'placeholder' => 'DDD']) !!}
            </div>
            <div class="col-sm-5">
              {!! Form::text('telefone', null, ['class' => 'form-control', 'placeholder' => 'Telefone']) !!}
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-6">
        {!! Form::text('card_number', null, ['class' => 'form-control', 'placeholder' => 'Número do cartão']) !!}
      </div>
    </div>
  </div>
</div>
