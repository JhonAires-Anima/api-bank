<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Deposits;
use App\Models\Transfers;
use App\Models\Withdrawals;
use Illuminate\Support\Facades\Schema;
use stdClass;
use Illuminate\Support\Facades\Mail;
use App\Mail\TokenMail;
class ApiController extends Controller
{
    public function sendResponse($result, $message)
    {
        $response = [
            "success" => true,
            "data" => $result,
            "message" => $message
        ];
        return response()->json($response, 200);
    }

    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            "success" => false,
            "error" => $error,
            "errorMessages" => $errorMessages
        ];
        return response()->json($response, $code);
    }

    public function createAccount($id, $email, $balance = 0) {
        $emailLength = strlen($email);
        $emailIsValid = false;
        $idIsValid = false;
                
        if ($emailLength !== 0) {
        if (strpos($email, '@')) {
            $emailIsValid=true;
        } else {
            $error = $this->sendError('Account Creation', 'Email is invalid.', 401);
            return $error;
        }
                    
        } else {
            $error = $this->sendError('Account Creation', 'Email must not be empty.', 401);
            return $error;
            }

            if (is_numeric($id)) {
                $idLength = strlen($id);

                if ( $idLength === 8 ) {
                    $idIsValid=true;
                } else if ( $idLength < 8 ) {
                    return $this->sendError('Account Creation', 'Id is too short.', 401);
                } else if ( $idLength > 8) {
                    return $this->sendError('Account Creation', 'Id is too long.', 401);
                } else {
                    return $this->sendError('Account Creation', 'Unknown error in id validation', 404);;
                }

                } else {
                    return $this->sendError('Account Creation', 'Id must be a number.', 401);
                }       

                if ($emailIsValid && $idIsValid) {
                    $newAccountId = Account::where('id', $id)->select('id')->exists();
                    $newAccountEmail = Account::where('email', $email)->select('email')->exists();

                    if ($newAccountEmail) {
                        return $this->sendError('Account Creation', 'Account with that email already exists', 404);
                    }

                    if ($newAccountId === true) {
                        return $this->sendError('Account Creation', 'Account already exists', 404);

                    }else {

                        if ($newAccountId === false) {
                        $Account = new Account();
                        $Account->id = $id;
                        $Account->email = $email;
                        $Account->balance = $balance;
                        $Account->save();
        
                        $res = new stdClass;
                        $res->id = $id;
                        $res->email = $email;
        
                        return $this->sendResponse($res, 'Account succesfully created', 201);
                    }
                }
            }
                
        return $this->sendError('Account Creation', 'Unexpected error in account creation', 404);
    }

    private function deposit($destination, $amount) {
        $accountExists = Account::where('id', $destination)->select('id')->exists();

        if ($accountExists) {
            $oldAmount = Account::where('id', $destination)->select('balance')->get();
            $newAmount = $oldAmount[0]->balance + $amount;
            
            Account::where('id', $destination)->update(['balance'=> $newAmount]);
            
            $Deposit = new Deposits;
            $Deposit->id_deposit;
            $Deposit->id_destino = $destination;
            $Deposit->monto = $amount;
            $Deposit->save();

            $res = Account::where('id', $destination)->select('id','balance')->get()[0];

            return $this->sendResponse($res, 'Ammount succesfully deposited', 200);

        }
        return $this->sendError('Deposit',"Account not found", 404);

    }

    private function withdrawal($origin, $amount) {
        $accountExists = Account::where('id', $origin)->select('id')->exists();

        if ($accountExists) {
            $oldAmount = Account::where('id', $origin)->select('balance')->get();
            $newAmount = $oldAmount[0]->balance - $amount;

            if ($newAmount >= 0) {
                 Account::where('id', $origin)->update(['balance'=> $newAmount]);
            
                $Withdrawal = new Withdrawals();
                $Withdrawal->id_withdrawals;
                $Withdrawal->id_origen = $origin;
                $Withdrawal->monto = $amount;
                $Withdrawal->save();

                $res = Account::where('id', $origin)->select('id','balance')->get()[0];

                return $this->sendResponse($res, 'Money withdrawn correctly', 200);
            }
           
            return $this->sendError('Withdrawal', 'Account money is less than requested money', 404);
        }
        return $this->sendError('Withdrawal','Account not found', 404);
    }

    private function transfer($origin, $destination, $amount) {
        $originAccountExists = Account::where('id', $origin)->select('id')->exists();
        $destinationAccountExists = Account::where('id', $destination)->select('id')->exists();

        if ($originAccountExists) {
            if ($destinationAccountExists) {
                $originOldAmount = Account::where('id', $origin)->select('balance')->get();
                $originNewAmount = $originOldAmount[0]->balance - $amount;
                
                $destinationOldAmount = Account::where('id', $destination)->select('balance')->get();
                $destinationNewAmount = $destinationOldAmount[0]->balance + $amount;

                if ($originNewAmount >= 0) {
                    Account::where('id', $origin)->update(['balance' => $originNewAmount]);
                    Account::where('id', $destination)->update(['balance' => $destinationNewAmount]);

                    $Transfer = new Transfers();
                    $Transfer->id_transfer;
                    $Transfer->id_origen = $origin;
                    $Transfer->id_destino = $destination;
                    $Transfer->monto = $amount;
                    $Transfer->save();

                    $res = new stdClass;
                    $res->origen = Account::where('id', $origin)->select('id','balance')->get()[0];
                    $res->destino = Account::where('id', $destination)->select('id','balance')->get()[0];

                    return $this->sendResponse($res, 'Transfer done!', 200);
                }

                return $this->sendError('Invalid transfer', 'Origen balance is less than requested amount', 404);
            }

            return $this->sendError('Invalid transfer', 'Destino not found', 404);
        }

        return $this->sendError('Invalid transfer', 'Origen not found', 404);
    }

    public function event(Request $request)
    {
        $requestType = $request->input('tipo');
        
        switch ( $requestType ) {
            case 'crear_cuenta':
                $id = $request->input('id');
                $email = $request->input('email');
                if (strlen($id) !== 0) {

                    if (strlen($email) !== 0) {
                        return $this->createAccount($id, $email);
                    }

                    return $this->sendError('Account Creation', "Email can't be empty", 404);
                    
                }

                return $this->sendError('Account Creation', "Id can't be empty", 404);
            break;
            
            case 'deposito':
                $destination = $request->input('destino');
                $amount = $request->input('monto');
                if (strlen($destination) !== 0 ) {
                    if (strlen($amount) !== 0 ) {
                        return $this->deposit($destination, $amount);
                    }
                    return $this->sendError('Deposit', "Monto can't be empty", 404);
                }

                return $this->sendError('Deposit', "Destino can't be empty", 404);
            break;

            case 'retiro':
                $origin = $request->input('origen');
                $amount = $request->input('monto');

                if (strlen($origin) !== 0 ) {

                    if (strlen($amount) !== 0 ) {
                        return $this->withdrawal($origin, $amount);
                    }

                    return $this->sendError('Withdrawal', "Monto can't be empty", 404);
                }

                return $this->sendError('Withdrawal', "Origen can't be empty", 404);
            break;

            case 'transferencia':
                $origin = $request->input('origen');
                $destination = $request->input('destino');
                $amount = $request->input('monto');
                $integerAmount = $amount + 0;

                if (strlen($origin) !== 0 ) {
                    if (strlen($destination) !== 0 ) {
                        if (strlen($amount) !== 0 && $integerAmount >= 1000 ) {
                            $token = random_int(100000, 999999);
                                /*$details = [
                                'title' => 'Mail from api bank',
                                'body' => $token
                            ];

                            Mail::to('jhon.aires@anima.edu.uy')->send(new TokenMail($details));
                            dd('email is sent'); */
                            return $token;
                        }

                        if (strlen($amount) !== 0) {
                            return $this->transfer($origin, $destination, $amount);
                        }

                        return $this->sendError('Invalid Transfer', "Monto can't be empty", 404);
                    }

                    return $this->sendError('Invalid Transfer', "Destino can't be empty", 404);
                }
                
                return $this->sendError('Invalid Transfer', "Origen can't be empty", 404);
                

                break;
            
            default:
                return $this->sendError('Invalid type', 'No type with that name', 404);
            break;
        }
    }

    public function balance(Request $request) {
        $id = $request->input('id');
        $accountExists = Account::where('id', $id)->select('id')->exists();

        if ($accountExists) {
            $res  = Account::where('id', $id)->select('balance')->get();

            return $this->sendResponse($res, 'Account balance', 200);
        }

        return $this->sendError('Invalid account', 'Account not found', 404);
    }

    public function reset() {
    
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('transfers');
        Schema::dropIfExists('deposits');
        Schema::dropIfExists('accounts');

        if (!Schema::hasTable('accounts')){
            
            Schema::create('accounts', function($table){
                $table->integer('id');
                $table->char('email', 200);
                $table->integer('balance');
                
                $table->primary('id');
            });
        }
        
        if (!Schema::hasTable('deposits')){
            
            Schema::create('deposits', function($table){
            $table->increments('id_deposit', 10);
            $table->integer('id_destino');
            $table->integer('monto');

            $table->foreign('id_destino')->references('id')->on('accounts');
            });

        }
        
        if (!Schema::hasTable('transfers')){

            Schema::create('transfers', function($table){
            $table->increments('id_transfer');
            $table->integer('id_origen');
            $table->integer('id_destino');
            $table->integer('monto');
            
            $table->foreign('id_origen')->references('id')->on('accounts');
            $table->foreign('id_destino')->references('id')->on('accounts');
            });
        }

        if (!Schema::hasTable('withdrawals')){

            Schema::create('withdrawals', function($table){
                $table->increments('id_withdrawals', 20);
                $table->integer('id_origen');
                $table->integer('monto');
            
                $table->foreign('id_origen')->references('id')->on('accounts');
            });
        }

        if (Schema::hasTable('accounts') && Schema::hasTable('deposits') && Schema::hasTable('transfers') && Schema::hasTable('withdrawals')) {
            return $this->sendResponse('Reset', 'Database Reseted correctly', 200);
        } else {
            return $this->sendError('Error when reset', 'Unexpected error', 404);
        }
    }
}
