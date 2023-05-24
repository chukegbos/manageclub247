<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Setting;
use App\Sale;
use App\Death;
use App\Fund;
use App\Item;
use App\Type;
use App\Payment;
use App\Member;
use App\PaymentDebit;
use App\Product;
use App\PaymentBank;
use App\PaymentPos;
use App\PaymentChannel;
use App\MemberAdditional;
use App\MemberCard;
use App\MemberEducation;
use App\MemberSection;
use App\Section;
use App\Suspend;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use DB;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('api');
    }


    public function membertype(Request $request)
    {
        $setting = Setting::findOrFail(1);
   
        $query = Type::where('id', '!=', NULL);

        if ($request->name) {
            $query->where('title', 'like', '%' . $request->name . '%');
        }

        if ($request->selected==0) {
            $params['types'] =  $query->get();
        }
        else{
            $params['types'] =  $query->paginate($request->selected);
        }
        
        $params['all'] = $query->count();

        return $params;
    }

    public function storemembertype(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string|max:191',
        ]);
       
        $memberType = Type::create([
            'title' => $request['title'],
        ]);

        return ['Message' => 'Updated'];
    }

    public function updatemembertype(Request $request, $id)
    {
        $memberType = Type::findOrFail($id);
        $this->validate($request, [
            'title' => 'required|string|max:191',
        ]);

        $memberType->update([
            'title' => $request['title'],
        ]);

        return ['Message' => 'Updated'];
    }

    public function destroymembertype(Request $request)
    {
        foreach ($request->selected as $id) {
            Type::Destroy($id);
        }
        return 'ok';  
    }

    public function membersection(Request $request)
    {
        $query = Section::where('id', '!=', NULL);

        if ($request->name) {
            $query->where('title', 'like', '%' . $request->name . '%');
        }

        if ($request->selected==0) {
            $params['types'] =  $query->get();
        }
        else{
            $params['types'] =  $query->paginate($request->selected);
        }
        
        $params['all'] = $query->count();

        return $params;
    }

    public function storemembersection(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string|max:191',
        ]);
       
        Section::create([
            'title' => $request['title'],
        ]);

        return ['Message' => 'Updated'];
    }

    public function updatemembersection(Request $request, $id)
    {
        $memberSection = Section::findOrFail($id);
        $this->validate($request, [
            'title' => 'required|string|max:191',
        ]);

        $memberSection->update([
            'title' => $request['title'],
        ]);

        return ['Message' => 'Updated'];
    }

    public function destroymembersection(Request $request)
    {
        foreach ($request->selected as $id) {
            Section::Destroy($id);
        }
        return 'ok';  
    }

    // public function index(Request $request)
    // {
    //     $params = [];
    //     set_time_limit(0);

    //     $query = User::where('deleted_at', NULL) ->where('role', 0)
    
    //     if ($request->state) {
    //         $query->where('state', $request->state);
    //     }

    //     if ($request->picture) {
    //         if ($request->picture==1) {
    //             $query->where('users.image', '!=', NULL);
    //         }
    //         elseif ($request->picture==2) {
    //             $query->where(function($qy) {
    //                 $qy->where('users.image', '')->orWhere('users.image', NULL);
    //             });
    //         }
    //     }

    //     if ($request->before) {
    //         $query->where('users.created_at', '<=', $request->before);
    //     }

    //     if($request->member_id) {
    //         // $query->where('users.unique_id', '<=', (int)$request->member_id);
    //         $query->where(function($qy) use($request) {
    //             $qy->where('users.unique_id', '<=', (int)$request->member_id)
    //             ->orWhere('default_esc_members.membership_id', '<=', (int)$request->member_id);
    //         });
    //     }

    //     if ($request->member_type) {
    //         $query->where('default_esc_members.member_type', $request->member_type);
    //     }

    //     if ($request->name) {
    //         $query->where(function($qy)use($request) {
    //             $qy->where('users.name', 'like', '%' . $request->name . '%')
    //             ->orWhere('users.unique_id', 'like', '%' . $request->name . '%')
    //             ->orWhere('users.phone', 'like', '%' . $request->name . '%')
    //             ->orWhere('users.email', 'like', '%' . $request->name . '%')
    //             ->orWhere('users.c_person', 'like', '%' . $request->name . '%');
    //         });
    //     }

    //     if ($request->debt) {
    //         if ($request->debt==1) {
    //             $query->where('users.debt', '>', 0);
    //         }
    //         elseif ($request->debt==2) {
    //             $query->where('users.debt', '<=', 0);
    //             // $query->where(function($qy) {
    //             //     $qy->where('users.debt', 0)->orWhere('users.debt', NULL);;
    //             // });
    //         }
    //     }
        
    //     if($request->transactions_after){
    //         $query->join('default_esc_payments', 'default_esc_members.id', '=', 'default_esc_payments.member_id')
    //             ->where('default_esc_payments.lastedit', '>=', $request->transactions_after)
    //             ->groupBy('default_esc_payments.member_id');
    //     }

    //     $query->select(
    //         'users.id as id',
    //         'users.unique_id as unique_id',
    //         'users.name as name',
    //         'users.state as state',
    //         'users.city as city',
    //         'users.email as email',
    //         'users.entrance_date as entrance_date',
    //         'users.created_at as created_at',
    //         'users.image as image',
    //         'users.access as access',
    //         'users.phone as phone',
    //         'users.debt as debt',
    //         'users.c_person as c_person',
    //         'users.approved as approved',
    //         'users.approved_by as approved_by',
    //         'users.door_access as door_access'
    //     );
        
    //     $all = $query->get();
    //     if ($request->selected==0) {
    //         $params['customers'] = $query->get();
    //     }
    //     else{
    //         $params['customers'] = $query->paginate($request->selected);
    //     }
        
    //     $params['all'] = count($all);
    //     $params['user'] = auth('api')->user();
    //     return $params;
    // }

    public function calculateDebt() {
        set_time_limit(0);
        $all = User::where('deleted_at', NULL) ->where('role', 0)->get();
        foreach ($all as $user) {
            $unique_id = $user->unique_id;
            $member = Member::where('deleted_at', NULL)->where('membership_id', $unique_id)->first();
            if($member){
                $amount = PaymentDebit::where('deleted_at', NULL)->where('member_id', $member->id)->where('status', 0)->sum('amount');
                $user = User::find($user->id);
                $user->debt = $amount;
                $user->update();
            }
        }
        return 'ok';
    }
    public function index(Request $request)
    {
        $params = [];
        set_time_limit(0);
        // $this->calculateDebt();
        $query = User::where('deleted_at', NULL) ->where('role', 0)->orderBy('name', 'asc');
    
        if ($request->state) {
            $query->where('state', $request->state);
        }

        if ($request->picture) {
            if ($request->picture==1) {
                $query->where('image', '!=', NULL);
            }
            elseif ($request->picture==2) {
                $query->where(function($qy) {
                    $qy->where('image', '')->orWhere('image', NULL);
                });
            }
        }

        if ($request->before) {
            $query->where('created_at', '<=', $request->before);
        }

        if($request->member_id) {
            $query->where('unique_id', '<=', (int)$request->member_id);
        }

        if ($request->name) {
            $query->where(function($qy)use($request) {
                $qy->where('name', 'like', '%' . $request->name . '%')
                ->orWhere('unique_id', 'like', '%' . $request->name . '%')
                ->orWhere('phone', 'like', '%' . $request->name . '%')
                ->orWhere('email', 'like', '%' . $request->name . '%')
                ->orWhere('c_person', 'like', '%' . $request->name . '%');
            });
        }

        if ($request->debt) {
            if ($request->debt==1) {
                $query->where('debt', '>', 0);
            }
            elseif ($request->debt==2) {
                $query->where('debt', '<=', 0);
                // $query->where(function($qy) {
                //     $qy->where('debt', 0)->orWhere('debt', NULL);;
                // });
            }
        }
        
        // if($request->transactions_after){
        //     $query->join('default_esc_payments', 'default_esc_members.id', '=', 'default_esc_payments.member_id')
        //         ->where('default_esc_payments.lastedit', '>=', $request->transactions_after)
        //         ->groupBy('default_esc_payments.member_id');
        // }

        $query->select(
            'id as id',
            'unique_id as unique_id',
            'name as name',
            'state as state',
            'city as city',
            'email as email',
            'entrance_date as entrance_date',
            'created_at as created_at',
            'image as image',
            'access as access',
            'phone as phone',
            'debt as debt',
            'c_person as c_person',
            'approved as approved',
            'approved_by as approved_by',
            'door_access as door_access'
        );
        
        $all = $query->get();
        if ($request->selected==0) {
            $params['customers'] = $query->get();
        }
        else{
            $params['customers'] = $query->paginate($request->selected);
        }
        
        $params['all'] = count($all);
        $params['user'] = auth('api')->user();
        return $params;
    }

    // public function all(Request $request)
    // {
    //     $params = [];
    //     set_time_limit(0);

    //     $query = User::where('users.deleted_at', NULL)
    //         ->where('default_esc_members.deleted_at', NULL)
    //         ->where('users.role', 0)
    //         ->join('default_esc_members', 'users.unique_id', '=', 'default_esc_members.membership_id')
    //         ->where('default_esc_members.member_type', '!=', 14)
    //         ->orderBy('default_esc_members.last_name', 'asc')
    //         ->orderBy('default_esc_members.first_name', 'asc');

    //     if($request->transactions_after){
    //         $query->join('default_esc_payments', 'default_esc_members.id', '=', 'default_esc_payments.member_id')
    //             ->where('default_esc_payments.lastedit', '>=', $request->transactions_after)
    //             ->groupBy('default_esc_payments.member_id');
    //     }

    //     if($request->member_id) {
    //         // $query->where('users.unique_id', '<=', (int)$request->member_id);
    //         $query->where(function($qy) use($request) {
    //             $qy->where('users.unique_id', '<=', (int)$request->member_id)
    //             ->orWhere('default_esc_members.membership_id', '<=', (int)$request->member_id);
    //         });
    //     }

    //     if ($request->state) {
    //         $query->where('users.state', $request->state);
    //     }

    //     if ($request->picture) {
    //         if ($request->picture==1) {
    //             $query->where('users.image', '!=', NULL);
    //         }
    //         elseif ($request->picture==2) {
    //             $query->where(function($qy) {
    //                 $qy->where('users.image', '')->orWhere('users.image', NULL);
    //             });
    //         }
    //     }

    //     if ($request->debt) {
    //         if ($request->debt==1) {
    //             $query->where('users.debt', '>', 0);
    //         }
    //         elseif ($request->debt==2) {
    //             $query->where('users.debt', '<=', 0);
    //             // $query->where(function($qy) {
    //             //     $qy->where('users.debt', 0)->orWhere('users.debt', NULL);;
    //             // });
    //         }
    //     }

    //     if ($request->debt) {
    //         if ($request->debt==1) {
    //             $query->where('users.debt', '>', 0);
    //         }
    //         elseif ($request->debt==2) {
    //             $query->where('users.debt', '<=', 0);
    //             // $query->where(function($qy) {
    //             //     $qy->where('users.debt', 0)->orWhere('users.debt', NULL);;
    //             // });
    //         }
    //     }

    //     if ($request->before) {
            
    //         $query->where('users.created_at', '<=', $request->before);
    //     }

    //     if ($request->member_type) {
    //         $query->where('default_esc_members.member_type', $request->member_type);
    //     }

    //     if ($request->name) {
    //         $query->where(function($qy)use($request) {
    //             $qy->where('users.name', 'like', '%' . $request->name . '%')
    //             ->orWhere('users.unique_id', 'like', '%' . $request->name . '%')
    //             ->orWhere('users.phone', 'like', '%' . $request->name . '%')
    //             ->orWhere('users.email', 'like', '%' . $request->name . '%')
    //             ->orWhere('users.c_person', 'like', '%' . $request->name . '%');
    //         });
    //     }

    //     $query->select(
    //         'users.unique_id as unique_id',
    //         'users.name as name',
    //         'users.email as email',
    //         'users.entrance_date as entrance_date',
    //         'users.image as image',
    //         'users.phone as phone',
    //         'users.debt as debt',
    //         'users.c_person as c_person',
    //     );
        
    //     $params['totalusers'] =  $query->get();
    //     return $params;
    // }

    public function all(Request $request)
    {
        $params = [];
        set_time_limit(0);
        // $this->calculateDebt();
        $query = User::where('deleted_at', NULL) ->where('role', 0)->orderBy('name', 'asc');

        if($request->member_id) {
            $query->where('unique_id', '<=', (int)$request->member_id);
        }

        if ($request->state) {
            $query->where('state', $request->state);
        }

        if ($request->picture) {
            if ($request->picture==1) {
                $query->where('image', '!=', NULL);
            }
            elseif ($request->picture==2) {
                $query->where(function($qy) {
                    $qy->where('image', '')->orWhere('image', NULL);
                });
            }
        }

        if ($request->debt) {
            if ($request->debt==1) {
                $query->where('debt', '>', 0);
            }
            elseif ($request->debt==2) {
                $query->where('debt', '<=', 0);
                // $query->where(function($qy) {
                //     $qy->where('debt', 0)->orWhere('debt', NULL);;
                // });
            }
        }

        if ($request->before) {
            $query->where('created_at', '<=', $request->before);
        }

        if ($request->name) {
            $query->where(function($qy)use($request) {
                $qy->where('name', 'like', '%' . $request->name . '%')
                ->orWhere('unique_id', 'like', '%' . $request->name . '%')
                ->orWhere('phone', 'like', '%' . $request->name . '%')
                ->orWhere('email', 'like', '%' . $request->name . '%')
                ->orWhere('c_person', 'like', '%' . $request->name . '%');
            });
        }

        $query->select(
            'unique_id as unique_id',
            'name as name',
            'email as email',
            'entrance_date as entrance_date',
            'image as image',
            'phone as phone',
            'debt as debt',
            'c_person as c_person',
        );
        
        $params['totalusers'] =  $query->get();
        return $params;
    }

    public function details(Request $request)
    {
        $params = [];
        $params['member_types'] = Type::get();
        $params['sections'] = Section::get();
        $params['states'] = DB::table('states')->get();
        return $params;
    }

    public function searchcustomer(Request $request)
    {
        $search_term = $request[0];
        $users = User::where('deleted_at', NULL)
            ->where('users.role', 0)
            ->where('users.name', 'like', '%' . $search_term . '%')
            ->orWhere('users.c_person', 'like', '%' . $search_term . '%')
            ->get();
        return $users;
    }

    public function view(Request $request, $unique_id)
    { 
        set_time_limit(0);
        $params = [];
        $params['user'] = User::where('deleted_at', NULL)->where('unique_id', $unique_id)->first();
 
        if($params['user']){
            $params['member'] = Member::where('deleted_at', NULL)->where('membership_id', $unique_id)->first();
            if (!$params['member']) {
                return ['error' => 'Member not found'];
            }
            
            $queryp = Payment::where('member_id', $params['member']->id)->latest();

            if ($request->start_date) {
                $queryp->where('created_at', '>=', $request->start_date);
            }

            if ($request->end_date) {
                $queryp->where('created_at', '<=', $request->end_date);
            }

            $params['payments'] = $queryp->get();
            $params['payment_sum'] = $queryp->sum('amount');

            $params['channels'] = PaymentChannel::get();
            $params['orders'] = Sale::where('sales.deleted_at', NULL)
                ->where('sales.status', 'concluded')
                ->where('sales.buyer', $params['user']->id)
                ->orderBy('sales.main_date', 'Desc')
                ->select(
                    'sales.id as id',
                    'sales.sale_id as sale_id',
                    'sales.initialPrice as initialPrice',
                    'sales.totalPrice as totalPrice',
                    'sales.discount as discount',
                    'sales.mop as mop',
                    'sales.status as status',
                    'sales.main_date as main_date'  
                )->take(5)
            ->get();

            $query1 = PaymentDebit::where('member_id', $params['member']->id)->where('status', 0)->latest();

                if ($request->start_date) {
                    $query1->where('start_date', '>=', $request->start_date);
                }

                if ($request->end_date) {
                    $query1->where('start_date', '<=', $request->end_date);
                }

            $params['payment_debts'] = $query1->get();
            $params['payment_debts_sum'] = $query1->sum('amount');

            $query_invoice = Sale::where('sales.deleted_at', NULL)
                ->where('sales.status', 'pending')
                ->where('sales.approved', 1)
                ->where('sales.buyer', $params['user']->id)
                ->orderBy('sales.main_date', 'Desc')
                ->join('users', 'sales.market_id', '=', 'users.id')
                ->where('users.deleted_at', NULL)
                ->select(
                    'sales.id as id',
                    'sales.sale_id as sale_id',
                    'sales.initialPrice as initialPrice',
                    'sales.totalPrice as totalPrice',
                    'sales.discount as discount',
                    'sales.mop as mop',
                    'sales.approved as approved',
                    'users.name as market_id',
                    'sales.status as status',
                    'sales.main_date as main_date'  
                );

                if ($request->start_date) {
                    $query_invoice->where('sales.main_date', '>=', $request->start_date);
                }

                if ($request->end_date) {
                    $query_invoice->where('sales.main_date', '<=', $request->end_date);
                }

                $params['invoices'] = $query_invoice
            ->get();

            $query_quote = Sale::where('sales.deleted_at', NULL)
                ->where('sales.status', 'pending')
                ->where('sales.approved', '!=', 1)
                ->where('sales.buyer', $params['user']->id)
                ->orderBy('sales.main_date', 'Desc')
                ->join('users', 'sales.market_id', '=', 'users.id')
                ->where('users.deleted_at', NULL)
                ->select(
                    'sales.id as id',
                    'sales.sale_id as sale_id',
                    'sales.initialPrice as initialPrice',
                    'sales.totalPrice as totalPrice',
                    'sales.discount as discount',
                    'sales.mop as mop',
                    'sales.approved as approved',
                    'users.name as market_id',
                    'sales.status as status',
                    'sales.main_date as main_date'  
                );

                if ($request->start_date) {
                    $query_invoice->where('sales.main_date', '>=', $request->start_date);
                }

                if ($request->end_date) {
                    $query_invoice->where('sales.main_date', '<=', $request->end_date);
                }

                $params['quotes'] = $query_quote
            ->get();

            $params['order_count'] = Sale::where('deleted_at', NULL)->where('status', 'concluded')->where('buyer', $params['user']->id)->count();

            $query3 = Sale::where('deleted_at', NULL)
                ->where('status', 'concluded')
                ->where('buyer', $params['user']->id);
                if ($request->start_date) {
                    $query3->where('sales.main_date', '>=', $request->start_date);
                }

                if ($request->end_date) {
                    $query3->where('sales.main_date', '<=', $request->end_date);
                }
                $params['value_order_count'] = $query3
            ->sum('totalPrice');

            $query2 = Fund::where('funds.deleted_at', NULL)
                ->where('funds.customer_id', $params['member']->id)
                ->join('default_esc_members', 'funds.user_id', '=', 'default_esc_members.id')
                ->select(
                    'default_esc_members.get_member as name',
                    'funds.mop as mop',
                    'funds.ref_id as ref_id',
                    'funds.amount as amount',
                    'funds.created_at as created_at'  
                )
                ->orderBy('funds.created_at', 'desc');

                if ($request->start_date) {
                    $query2->where('funds.created_at', '>=', $request->start_date);
                }

                if ($request->end_date) {
                    $query2->where('funds.created_at', '<=', $request->end_date);
                }
                $params['funds'] = $query2
            ->get();

                $params['sum_fund'] = $query2->sum('funds.amount');

            //$query5 = Item::where('sales.deleted_at', NULL)
                //->join('sales', 'items.code', '=', 'sales.sale_id')
            $query5 = Item::where('items.deleted_at', NULL)
                ->join('sales', 'items.code', '=', 'sales.sale_id')
                ->where('sales.deleted_at', NULL)
                ->where('sales.buyer', $params['user']->id)
                ->where('sales.status', 'concluded')
                ->orderBy('sales.main_date', 'desc')
                ->select(
                    'items.product_name as name',
                    'items.code as ref_id',
                    'items.qty as qty',
                    'items.totalPrice as amount',
                    'items.created_at as date'  
                );

                if ($request->start_date) {
                    $query5->where('items.created_at', '>=', $request->start_date);
                }

                if ($request->end_date) {
                    $query5->where('items.created_at', '<=', $request->end_date);
                }

                $params['items'] = $query5->orderBy('sales.main_date', 'desc')
            ->get();

            $query6 = Item::where('items.deleted_at', NULL)
                ->join('sales', 'items.code', '=', 'sales.sale_id')
                ->where('sales.deleted_at', NULL)
                ->where('sales.buyer', $params['user']->id)
                ->where('sales.status', 'concluded')
                ->orderBy('sales.main_date', 'desc');

                if ($request->start_date) {
                    $query6->where('items.created_at', '>=', $request->start_date);
                }

                if ($request->end_date) {
                    $query6->where('items.created_at', '<=', $request->end_date);
                }

                $params['item_count'] = $query6
            ->count();

            
            $params['additional'] = MemberAdditional::where('member_id', $params['member']->id)->first();
            $params['card_numbers'] = MemberCard::where('member_id', $params['member']->id)->get();
            $params['educations'] = MemberEducation::where('member_id', $params['member']->id)->get();
            $params['sections'] = MemberSection::where('default_esc_member_sections.member_id', $params['member']->id)
                ->join('default_esc_sections', 'default_esc_member_sections.section_id', '=', 'default_esc_sections.id')
                ->get();

            

            $params['banks'] =  PaymentBank::get();
            $params['pos'] = PaymentPos::where('deleted_at', NULL)->get();
            return $params;
        }
        else{
            return ['error' => 'Member not found'];
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'last_name' => 'required|string|max:191',
            'first_name' => 'required|string|max:191',
            //'email' => 'required|string|max:191|email|unique:users',
            //'phone_1' => 'required|string|max:19',
            //'state' => 'required',
            //'address' => 'required|string|max:191',
            'member_type' => 'required',
            //'dob' => 'required',
            'membership_id' => 'required'
        ]);
        set_time_limit(0);
        $getUser = User::where('deleted_at', NULL)->where('unique_id', $request->membership_id)->first();
        if ($getUser) {
            $error = 'Member with membership ID #'.$request->membership_id.' already exist.';
            return ['error' => $error];
        }

        $setting = Setting::findOrFail(1);

        if ($request->image) {
            $image = time().'.' . explode('/', explode(':', substr($request->image, 0, strpos($request->image, ';')))[1])[1];
            \Image::make($request->image)->save('img/members/'.$image);
        } 
        else {
            $image = NULL;
        }
        if ($request->email) {
            $email = $request->email;
        }
        else{
            $email = $request['membership_id'].'@enugusportsclub.org';
        }
        $user = User::create([
            'unique_id' => $request['membership_id'],
            'entrance_date' => $request['entrance_date'],
            'name' => $request['last_name'].' '.$request['first_name'].' '.$request['middle_name'],
            'c_person' => $request['last_name'].' '.$request['first_name'].' '.$request['middle_name'],
            'email' => $email,
            'phone' => $request['phone_1'],
            'dob' => $request['dob'],
            'image' => $image,
            'address' => $request['address'],
            'credit_unit' => 0,
            'door_access' => 0,
            'role' => 0,
            'password' => Hash::make('Father@1989'),
        ]);

        $member = Member::create([
            'membership_id' => $request['membership_id'],
            'last_name' => $request['last_name'],
            'first_name' => $request['first_name'],
            'middle_name' => $request['middle_name'],
            'email' => $email,
            'phone_1' => $request['phone_1'],
            'phone_2' => $request['phone_2'],
            'state' => $request['state'],
            'city' => $request['city'],
            'city_resident' => $request['city_resident'],
            'gender' => $request['gender'],
            'country' => $request['country'],
            'address' => $request['address'],
            'office_address' => $request['office_address'],
            'lga' => $request['lga'],
            'state_of_origin' => $request['state_of_origin'],
            'home_town' => $request['home_town'],
            'marital_status' => $request['marital_status'],
            'spouse_name' => $request['spouse_name'],
            'children' => $request['children'],
            'member_type' => $request['member_type'],
            'image' => $image,
        ]);

        $memberAdditional = MemberAdditional::create([
            'member_id' => $member->id,
            'kin_name' => $request['kin_name'],
            'kin_address' => $request['kin_address'],
            'kin_phone_1' => $request['kin_phone_1'],
            'kin_phone_2' => $request['kin_phone_2'],
            'kin_relationship' => $request['kin_relationship'],
            'beneficiary_name' => $request['beneficiary_name'],
            'beneficiary_address' => $request['beneficiary_address'],
            'beneficiary_phone_1' => $request['beneficiary_phone_1'],
            'beneficiary_phone_2' => $request['beneficiary_phone_2'],
            'beneficiary_relationship' => $request['beneficiary_relationship'],
            'sponsor_1' => $request['sponsor_1'],
            'sponsor_2' => $request['sponsor_2'],
        ]);

        if($request->card_numbers){
            foreach ($request->card_numbers as $item) {
                $memberCard = MemberCard::create([
                    'member_id' => $member->id,
                    'card_number' => $item['card_number'],
                ]);
            }
        }

        if($request->educationItems){
            foreach ($request->educationItems as $item) {
                MemberEducation::create([
                    'member_id' => $member->id,
                    'level' => $item['level'],
                    'institution' => $item['institution'],
                    'degree' => $item['degree'],
                ]);
            }
        }
        
        if($request->sections){
            foreach ($request->sections as $section_id) {
                MemberSection::create([
                    'member_id' => $member->id,
                    'section_id' => $section_id,
                ]);
            }
        }

        return ['Update' => 'Updated'];
    }


    public function suspend(Request $request)
    {
        $this->validate($request, [
            'start_date' => 'required',
            'end_date' => 'required',
        ]);
        set_time_limit(0);
        $getUser = User::where('deleted_at', NULL)->where('unique_id', $request->id)->first();
        
        if (!$getUser) {
            $error = 'Member with membership ID #'.$request->membership_id.' does not exist.';
            return ['error' => $error];
        }

        $getUser->update([
            'door_access' => 0,
        ]);



        $member = Member::where('deleted_at', NULL)->where('membership_id', $request->id)->first();
        
        Suspend::create([
            'membership_id' => $request['id'],
            'start_date' => $request['start_date'],
            'end_date' => $request['end_date'],
            'suspended_by' => auth('api')->user()->id,
            'former_type' => $member->member_type,
        ]);

        $member->update([
            'member_type' => 15,
        ]);

        return ['Update' => 'Updated'];
    }

    public function unsuspend(Request $request)
    {
        $this->validate($request, [
            'reason' => 'required',
        ]);
        set_time_limit(0);
        $getUser = User::where('deleted_at', NULL)->where('unique_id', $request->id)->first();
        
        if (!$getUser) {
            $error = 'Member with membership ID #'.$request->membership_id.' does not exist.';
            return ['error' => $error];
        }

        $getUser->update([
            'door_access' => 1,
        ]);


        $member = Member::where('deleted_at', NULL)->where('membership_id', $request->id)->first();
        
        $getSus = Suspend::where('deleted_at', NULL)->where('membership_id', $request->id)->first();
        $getSus->status = 1;
        $getSus->unsuspended_by = auth('api')->user()->id;
        $getSus->reason = $request->reason;
        $getSus->update();


        $member->update([
            'member_type' => $getSus->former_type,
        ]);

        return ['Update' => 'Updated'];
    }

    public function late($unique_id)
    {
       
        $getUser = User::where('deleted_at', NULL)->where('unique_id', $unique_id)->first();
        
        if (!$getUser) {
            $error = 'Member with membership ID #'.$unique_id.' does not exist.';
            return ['error' => $error];
        }

        $getUser->update([
            'door_access' => 0,
        ]);
        $member = Member::where('deleted_at', NULL)->where('membership_id', $unique_id)->first();

        $member->update([
            'member_type' => 14,
        ]);

        Death::create([
            'member_id' => $unique_id,
            'authoriser' => auth('api')->user()->id,
        ]);
        return ['Update' => 'Updated'];
    }

    public function approve($unique_id)
    {
       
        $getUser = User::where('deleted_at', NULL)->where('unique_id', $unique_id)->first();
        
        if (!$getUser) {
            $error = 'Member with membership ID #'.$unique_id.' does not exist.';
            return ['error' => $error];
        }

        $suspend = Suspend::where('deleted_at', NULL)->where('status', 0)->where('membership_id', $unique_id)->first();
        $death = Death::where('deleted_at', NULL)->where('member_id', $unique_id)->first();
        $debit = PaymentDebit::where('deleted_at', NULL)->where('member_id', $unique_id)->where('period', 0)->first();

        if (!$suspend && !$death && !$debit && $getUser->phone) {
            $door_access = 1;
        }
        else{
            $door_access = 0;
        }
        $getUser->update([
            'door_access' => $door_access,
            'approved' => 1,
            'approved_by' => auth('api')->user()->id,
        ]);
   
      
        return ['Update' => 'Updated'];
    }

    public function dooraccess($unique_id)
    {
       
        $getUser = User::where('deleted_at', NULL)->where('unique_id', $unique_id)->first();
        
        if (!$getUser) {
            $error = 'Member with membership ID #'.$unique_id.' does not exist.';
            return ['error' => $error];
        }


        if ($getUser->door_access==0) {
            $door_access = 1;
        }
        else{
            $door_access = 0;
        }
        $getUser->update([
            'door_access' => $door_access,
        ]);
   
      
        return ['Update' => 'Updated'];
    }
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $this->validate($request, [
            'last_name' => 'required|string|max:191',
            'first_name' => 'required|string|max:191',
            //'email' => 'required|string|max:191|email|unique:users',
            //'phone_1' => 'required|string|max:19',
            //'state' => 'required',
            //'address' => 'required|string|max:191',
            //'member_type' => 'required',
            //'dob' => 'required',
            'membership_id' => 'required'
        ]);

        set_time_limit(0);

        $setting = Setting::findOrFail(1);

        if ($request->image) {
            $image = time().'.' . explode('/', explode(':', substr($request->image, 0, strpos($request->image, ';')))[1])[1];

            \Image::make($request->image)->save('img/members/'.$image);
        } 
        else {
            $image = $user->image;
        }

        $user->update([
            'unique_id' => $request['membership_id'],
            'entrance_date' => $request['entrance_date'],
            'name' => $request['last_name'].' '.$request['first_name'].' '.$request['middle_name'],
            'c_person' => $request['last_name'].' '.$request['first_name'].' '.$request['middle_name'],
            'email' => $request['email'],
            'phone' => $request['phone_1'],
            'dob' => $request['dob'],
            'image' => $image,
            'address' => $request['address'],
        ]);

        $member = Member::where('membership_id', $request['membership_id'])->first();
        $member->update([
            'membership_id' => $request['membership_id'],
            'last_name' => $request['last_name'],
            'first_name' => $request['first_name'],
            'middle_name' => $request['middle_name'],
            'email' => $request['email'],
            'phone_1' => $request['phone_1'],
            'phone_2' => $request['phone_2'],
            'state' => $request['state'],
            'city_resident' => $request['city_resident'],
            'gender' => $request['gender'],
            'country' => $request['country'],
            'address' => $request['address'],
            'office_address' => $request['office_address'],
            'lga' => $request['lga'],
            'state_of_origin' => $request['state_of_origin'],
            'home_town' => $request['home_town'],
            'marital_status' => $request['marital_status'],
            'spouse_name' => $request['spouse_name'],
            'children' => $request['children'],
            'member_type' => $request['member_type'],
            'image' => $image,
        ]);

        $memberAdditional = MemberAdditional::where('member_id', $member->id)->first();
        if ($memberAdditional) {
            $memberAdditional->update([
                'member_id' => $member->id,
                'kin_name' => $request['kin_name'],
                'kin_address' => $request['kin_address'],
                'kin_phone_1' => $request['kin_phone_1'],
                'kin_phone_2' => $request['kin_phone_2'],
                'kin_relationship' => $request['kin_relationship'],
                'beneficiary_name' => $request['beneficiary_name'],
                'beneficiary_address' => $request['beneficiary_address'],
                'beneficiary_phone_1' => $request['beneficiary_phone_1'],
                'beneficiary_phone_2' => $request['beneficiary_phone_2'],
                'beneficiary_relationship' => $request['beneficiary_relationship'],
                'sponsor_1' => $request['sponsor_1'],
                'sponsor_2' => $request['sponsor_2'],
            ]);
        }

        else{
            $memberAdditional = MemberAdditional::create([
                'member_id' => $member->id,
                'kin_name' => $request['kin_name'],
                'kin_address' => $request['kin_address'],
                'kin_phone_1' => $request['kin_phone_1'],
                'kin_phone_2' => $request['kin_phone_2'],
                'kin_relationship' => $request['kin_relationship'],
                'beneficiary_name' => $request['beneficiary_name'],
                'beneficiary_address' => $request['beneficiary_address'],
                'beneficiary_phone_1' => $request['beneficiary_phone_1'],
                'beneficiary_phone_2' => $request['beneficiary_phone_2'],
                'beneficiary_relationship' => $request['beneficiary_relationship'],
                'sponsor_1' => $request['sponsor_1'],
                'sponsor_2' => $request['sponsor_2'],
            ]);
        }

        $memberCards = MemberCard::where('member_id', $member->id)->get();
        foreach ($memberCards as $card) {
            MemberCard::Destroy($card->id);
        }

        foreach ($request->card_numbers as $card) {
            MemberCard::create([
                'member_id' => $member->id,
                'card_number' => $card['card_number'],
            ]);
        }
        

        $memberEducations = MemberEducation::where('member_id', $member->id)->get();
        foreach ($memberEducations as $edu) {
            MemberEducation::Destroy($edu->id);
        }

        foreach ($request->educationItems as $item) {
            MemberEducation::create([
                'member_id' => $member->id,
                'level' => $item['level'],
                'institution' => $item['institution'],
                'degree' => $item['degree'],
            ]);
        }
        
        $memberSections = MemberSection::where('member_id', $member->id)->get();
        foreach ($memberSections as $sec) {
            MemberSection::Destroy($sec->id);
        }

        foreach ($request->sections as $section_id) {
            MemberSection::create([
                'member_id' => $member->id,
                'section_id' => $section_id,
            ]);
        }

        return ['Message' => 'Updated'];
    }

    public function edit($id)
    {
        $params = [];
        set_time_limit(0);

        $params['user'] = User::Find($id);
        if ($params['user']) {
            $params['member'] = Member::where('membership_id', $params['user']->unique_id)->first();
            $params['memberAdditional'] = MemberAdditional::where('member_id', $params['member']->id)->first();
            $params['MemberCards'] = MemberCard::where('member_id', $params['member']->id)->get();
            $params['MemberEducation'] = MemberEducation::where('member_id', $params['member']->id)->get();
            $params['MemberSection'] = MemberSection::where('member_id', $params['member']->id)->pluck('section_id')->toArray();
           return $params;
        }
        else{
            return ['error' => 'Customer not found'];
        }
    }

    public function delete(Request $request)
    {
        foreach ($request->selected as $id) {
            User::Destroy($id);
        }
        return 'ok';
        
    }

    public function destroy($id)
    {
        User::destroy($id);
        return ['Message' => 'Deleted'];
    }
}
