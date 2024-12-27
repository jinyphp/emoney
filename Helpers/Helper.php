<?php

function user_balance($id=null) {
    if(!$id){
        $id = Auth::user()->id;;
    }

    $emoney = DB::table('user_emoney')->where('user_id', $id)->first();
    if($emoney){
        return $emoney->balance;
    }

    return 0;
}

/**
 * 회원 입금 적립금 추가
 */
function user_emoney_deposit($email, $amount) {
    $user = DB::table('users')->where('email', $email)->first();
    if($user){
        $user_id = $user->id;
        $row = DB::table('user_emoney')
            ->where('email', $email)->first();
        if($row){
            $row->balance += $amount;
            DB::table('user_emoney')
                ->where('email', $email)
                ->update([
                    'balance' => $row->balance,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        } else {
            DB::table('user_emoney')
                ->insert([
                    'email' => $email,
                    'user_id' => $user_id,
                    'name' => $user->name,
                    'balance' => $amount,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        }

        return true;
    }

    return false;
}



/**
 * 회원 입금 적립금 사용
 */
function user_emoney_withdraw($email, $amount) {
    $user = DB::table('users')->where('email', $email)->first();
    if($user){
        $user_id = $user->id;
        $row = DB::table('user_emoney')
            ->where('email', $email)->first();
        if($row){
            $row->balance -= $amount;
            DB::table('user_emoney')
                ->where('email', $email)
                ->update([
                    'balance' => $row->balance,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        } else {
            DB::table('user_emoney')
                ->insert([
                    'email' => $email,
                    'user_id' => $user_id,
                    'name' => $user->name,
                    'balance' => "-".$amount,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        }

        return true;
    }

    return false;
}
