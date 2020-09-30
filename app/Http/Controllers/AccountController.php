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
        
        return response()->json(['transactions' => $client->transactions]);
    }
    
    /**
     * Deposit the given amount.
     *
     * @return \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function deposit(Request $request)
    {
        $amount = $request->amount;
        
        $client = auth('api')->user();
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
     * @return \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function withdraw(Request $request)
    {
        $amount = $request->amount;
        
        $client = auth('api')->user();
        if ($client->balance < $amount) {
            return $this->respondError('invalid amount');
        }

        $client->balance -= $amount;
        
        $client->save();
        $client->refresh();

        $this->addTransaction('width', $amount, $client->id);
        
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
     * @return \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function transfer(Request $request)
    {
        $amount = $request->amount;
        
        $client = auth('api')->user();
        if ($client->balance < $amount) {
            return $this->respondError('invalid amount');
        }
        
        $account = Client::where('account', $request->account);
        if (! $account->exists()) {
            return $this->respondError('account not found');
        } else {
            $to = $account->first();
        }      
        
        $client->balance -= $amount;
        $to->balance += $amount;
        
        $client->save();
        $client->refresh();
        
        $to->save();
        $to->refresh();

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
     * @param  string  $type    Transaction's type
     * @param  string  $amount  Amount
     * @param  integer $id      Client's id
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
