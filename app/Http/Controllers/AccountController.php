<?php

namespace App\Http\Controllers;

use App\Transaction;
use App\User as Client;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Create a new AccountController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    /**
     * Display the client's balance.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBalance()
    {
        $client = auth('api')->user();
        
        return response()->json(['balance' => $client->balance]);
    }
    
    /**
     * Display the client's transactions.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTransactions()
    {
        $client = auth('api')->user();
        
        return response()->json(['transactions' => array_reverse($client->transactions->toArray())]);
    }
    
    /**
     * Deposit the given amount.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function deposit(Request $request)
    {
        $amount = $request->amount;
        $client = auth('api')->user();
        
        if ($amount <= 0) {
            return $this->respondError('invalid amount');
        }

        $client->balance += $amount;
        
        $client->save();
        $client->refresh();

        $this->addTransaction('deposit', $amount, $client->id);
        
        return response()->json(
            [
                'type'    => 'deposit',
                'balance' => $client->balance,
                'amount'  => $amount,
            ],
            200, 
            [], 
            JSON_NUMERIC_CHECK
        );
    }
    
    /**
     * Withdraw the given amount.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function withdraw(Request $request)
    {
        $amount = $request->amount;
        $client = auth('api')->user();
        
        if ($amount <= 0 || $client->balance < $amount) {
            return $this->respondError('invalid amount');
        }

        $client->balance -= $amount;
        
        $client->save();
        $client->refresh();

        $this->addTransaction('withdraw', $amount, $client->id);
        
        return response()->json(
            [
                'type'    => 'withdraw',
                'balance' => $client->balance,
                'amount'  => $amount,
            ],
            200, 
            [], 
            JSON_NUMERIC_CHECK
        );
    }
    
    /**
     * Withdraw the given amount.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function transfer(Request $request)
    {
        $amount = $request->amount;
        $client = auth('api')->user();
        
        if ($amount <= 0 || $client->balance < $amount) {
            return $this->respondError('invalid amount');
        }
        
        $account = Client::where('account', $request->account);
        if (! $account->exists()) {
            return $this->respondError('account not found');
        }
        
        $to = $account->first();
        
        $client->balance -= $amount;
        $to->balance     += $amount;

        $to->save();
        $to->refresh();
        
        $client->save();
        $client->refresh();
        
        $this->addTransaction(
            'transfer_to',
            $amount, 
            $client->id, 
            $to->account
        );
        
        $this->addTransaction(
            'transfer_from', 
            $amount, 
            $to->id, 
            $client->account
        );
        
        return response()->json(
            [
                'type'    => 'transfer',
                'balance' => $client->balance,
                'amount'  => $amount,
                'from'    => $client->account,
                'to'      => [
                    'name'    => $to->name,
                    'account' => $to->account,
                ],
            ],
            200, 
            [], 
            JSON_NUMERIC_CHECK
        );
    }

    /**
     * Request error.
     *
     * @param  string $msg
     * @return \Illuminate\Http\Response
     */
    public function respondError(string $message)
    {
        return response()->json(['error' => $message], 403);
    }


    /**
     * Register transactions
     *
     * @param  string  $type    Transaction type
     * @param  float   $amount  Amount
     * @param  integer $id      Client id
     * @param  integer $account Account
     * @return void
     */
    private function addTransaction(
        string $type, 
        float $amount,
        int $id, 
        string $account = null
    ) {
        $transaction          = new Transaction;
        $transaction->type    = $type;
        $transaction->amount  = $amount;
        $transaction->account = $account;
        $transaction->user_id = $id;
        
        $transaction->save();
    }
}
