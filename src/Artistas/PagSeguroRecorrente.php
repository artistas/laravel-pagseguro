<?php
namespace Artistas\PagSeguro;

class PagSeguroRecorrente extends PagSeguroClient
{
    /**
     * Define os dados do plano.
     *
     * @param array $preApproval
     *
     * @return $this
     */
    public function setPreApprovalRequest(array $preApproval){
        $preApproval = [
            'email' => $this->email,
            'token' => $this->token,
            'preApprovalName'     => $this->sanitize($preApproval, 'preApprovalName'),
            'preApprovalCharge' => $this->sanitize($preApproval, 'preApprovalCharge'),
            'preApprovalPeriod'    => $this->sanitize($preApproval, 'preApprovalPeriod'),
            'preApprovalCancelUrl'    => $this->sanitize($preApproval, 'preApprovalCancelUrl'),
            'preApprovalAmountPerPayment'    => $this->sanitizeMoney($preApproval, 'preApprovalAmountPerPayment'),
            'preApprovalMembershipFee'    => $this->sanitizeMoney($preApproval, 'preApprovalMembershipFee'),
            'preApprovalTrialPeriodDuration'    => $this->sanitizeNumber($preApproval, 'preApprovalTrialPeriodDuration'),
            'preApprovalExpirationValue'    => $this->sanitizeNumber($preApproval, 'preApprovalExpirationValue'),
            'preApprovalExpirationUnit'    => $this->sanitize($preApproval, 'preApprovalExpirationUnit'),
            'maxUses'    => $this->sanitizeNumber($preApproval, 'maxUses'),
        ];

        $this->validatePreApproval($preApproval);        

        return (string) $this->sendTransaction($preApproval, $this->url['request'])->code;
    }

    /**
     * Valida os dados contidos na array de criação de um plano.
     *
     * @param array $preApproval
     */
    private function validatePreApproval(array $preApproval)
    {
        $rules = [
            'preApprovalName'     => 'required',
            'preApprovalCharge' => 'required',
            'preApprovalPeriod'    => 'required',
            'preApprovalCancelUrl'    => 'url',
            'preApprovalAmountPerPayment'    => 'required|numeric|between:1.00,2000.00',
            'preApprovalMembershipFee'    => 'numeric|between:0.00,1000000.00',
            'preApprovalTrialPeriodDuration'    => 'integer|between:1,1000000',
            'preApprovalExpirationValue'    => 'integer|between:1,1000000',
            'maxUses'    => 'integer|between:1,1000000',
        ];

        $this->validate($preApproval, $rules);
    }
}