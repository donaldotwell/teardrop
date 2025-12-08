<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="mx-auto my-0 overflow-y-scroll overflow-x-hidden">

<input type="checkbox" class="absolute hidden" id="theme"> <!-- checked dark theme -->

<div class="bg-back">

    <div class="min-h-full w-[98%] 2xl:max-w-5xl mx-auto bg-back">
        <header class="bg-abacus 2xl:bg-back rounded-b-md w-full z-10 flex flex-col-reverse 2xl:flex-col sticky 2xl:static top-0 mb-2">

            <div class="h-[65px] my-0.5 hidden 2xl:block">

                <div class="h-[95%]  mx-auto w-full flex justify-between ">

                    <div class="float-left text-left w-[485px] mt-6 ml-3">
                        <a class="flex" href="/">
                            <!-- Christmas Logo -->

                        </a><a class="flex relative" href="/">
                            <div style="max-width:295px;width:100%; height: 51px;" class="bg-center bg-contain bg-no-repeat picture-logo"></div>

                            <!-- hat -->
                            <div class="santa-hat absolute"></div>
                            <!-- tree -->

                            <div class="weedtainer self-end z-30">
                                <div class="weed-tree">
                                    <div class="weed-triangle1"></div>
                                    <div class="weed-triangle2"></div>
                                    <div class="weed-triangle3"></div>
                                    <div class="leaf or1">
                                        <div class="shine"></div>
                                    </div>
                                    <div class="leaf or2">
                                        <div class="shine"></div>
                                    </div>
                                    <div class="leaf or3">
                                        <div class="shine"></div>
                                    </div>
                                    <div class="leaf or4">
                                        <div class="shine"></div>
                                    </div>
                                    <div class="leaf or5">
                                        <div class="shine"></div>
                                    </div>
                                    <div class="leaf or6">
                                        <div class="shine"></div>
                                    </div>
                                    <div class="trunk"></div>
                                    <div class="star"></div>
                                </div>
                                <div class="gift-Box"></div>
                            </div>

                            <!-- drunk santa -->

                            <div class="z-20 absolute santa"></div>
                        </a>

                        <!-- End Christmas Logo -->


                    </div>



                    <div class="float-left h-[95px] relative ">

                        <div class="h-16 px-3 py-1 rounded-md border-solid border-[1.5px] border-transparent flex justify-center items-center">
                            <div class="" style="width:55px;">
                                <a class="std" href="/profile/49c8fe6a4f452a024bcda0cf" target="_blank">
                                    <div style="width: 47px; height: 47px; border-radius:50%;" class="picture picture-user"></div>
                                </a>
                            </div>

                            <div class="h-full flex flex-col items-start justify-center font-bold" style="width:220px;">


                                <span>Logged in as <a class="hover:text-abacus2" href="/profile/49c8fe6a4f452a024bcda0cf">pussycat</a>&nbsp; <a class="bg-abacus hover:bg-abacus2 text-white px-2 py-0.5 rounded" href="/logout">Logout</a></span>
                                <span>BTC: <a class="hover:text-abacus2" href="/balance">0.00000000</a> / XMR: <a class="std" href="/balance?crypto=xmr">0.00000000</a></span>


                            </div>
                        </div>
                    </div>
                </div>

            </div>



            <input id="nav" type="checkbox" name="nav" class="absolute hidden">

            <label for="nav" id="cover" class="hidden absolute z-10  h-screen w-screen left-1/2 top-0 -translate-x-1/2" style="background-image: linear-gradient(to left,rgba(6,46,68,.4),rgba(6,46,68,.5));"></label>

            <!-- CURRENCIES PRICES -->

            <div class="hidden overflow-hidden z-20 2xl:flex bg-abacus px-1.5 py-3 2xl:py-0 2xl:rounded-b-none 2xl:rounded-t-md rounded-b-md rounded-t-none text-right w-auto self-end">
                <div class="text-white min-w-[450px] text-center flex justify-around relative" style="font-size:11px !important;">

                    <span class="text-white text-center"><span class="green">▲</span>USD 92,674.82</span>&nbsp;


                    <span class="text-white text-center"><span class="green">▲</span>CAD 133,159.61</span>&nbsp;


                    <span class="text-white text-center"><span class="green">▲</span>EUR 88,660.69</span>&nbsp;


                    <span class="text-white text-center"><span class="green">▲</span>AUD 148,510.90</span>&nbsp;


                    <span class="text-white text-center"><span class="green">▲</span>GBP 73,508.22</span>&nbsp;
                </div>
            </div>

            <!-- NAVIGATION MENU -->


            <div id="nav-menu" class="w-full hidden 2xl:flex flex-col 2xl:flex-row items-center justify-between bg-abacus rounded-b-md rounded-tl-md">



                <div class="flex flex-col gap-1 2xl:gap-0 w-full h-14 2xl:w-auto 2xl:flex-row items-start 2xl:items-center">

                    <div class="flex 2xl:hidden w-full items-center justify-evenly mb-2.5">
                        <a href="" class="h-[22px] w-[198px]">
                            <div class="bg-no-repeat bg-left logo-mobile bg-contain h-[22px]"></div>
                        </a>
                        <label for="nav" class="border-solid border-[1px] hover:rotate-90 text-white rounded-full scale-110"><i class="gg-close"></i></label>
                    </div>

                    <span class="block 2xl:hidden w-full h-[1px] bg-white">&nbsp;</span>

                    <!-- user panel - mobile bar -->

                    <div class="w-full flex 2xl:hidden flex-col gap-1 items-center px-1 py-1.5 text-white bg-abacus">


                        <!-- USER PHOTO TRUST LEVEL AND USERNAME notifications too -->
                        <div class="grid grid-cols-[65px,auto] space-y-2 items-center w-full h-full">


                            <a href="/profile/49c8fe6a4f452a024bcda0cf" class="h-[60px] w-[60px] border-solid border-white bg-white overflow-hidden rounded-full">
                                <div class="picture picture-user h-full w-full"></div>
                            </a>

                            <div class="flex flex-col items-start px-1 w-auto overflow-x-hidden">
                                <a class="text-base text-white font-bold underline" href="/profile/49c8fe6a4f452a024bcda0cf">pussycat</a>
                                <div class="flex items-center text-sm">Trust Level:<span class="bg-lvl1 text-abacus font-bold rounded px-1 py-0.5 mx-1">1</span></div>
                            </div>

                            <div class="col-span-2 flex flex-col items-center">
                                <a href="/notifications" class="bg-white hover:bg-transparent text-abacus hover:text-white border-solid border-[1px] border-transparent hover:border-white font-bold rounded py-2 w-full text-center flex items-center justify-center gap-1"><div class="scale-[90%]"><i class="gg-bell animate-wiggle"></i></div> Notifications (1)</a>
                            </div>
                        </div>


                        <!-- BALANCE -->

                        <div class="w-full bg-white text-abacus rounded-md flex flex-col items-center">
                            <a href="/balance" class="text-sm font-bold flex items-center gap-2"><div class="scale-[90%]"><i class="gg-credit-card"></i></div>BALANCE</a>
                            <span class="w-full h-[1px] bg-abacus"></span>
                            <div class="flex items-center w-full justify-evenly">
                                <!-- btc -->
                                <div class="w-1/2 flex flex-col items-center py-1">
                                    <div class="flex gap-1 items-center">
                                        <div class="btc bg-center bg-no-repeat h-6 w-[17px]"></div>Bitcoin
                                    </div>
                                    <a href="/balance" class="bg-abacus hover:bg-abacus2 text-white rounded px-0.5 font-bold text-sm">0.00000000</a>
                                </div>
                                <!-- xmr -->
                                <div class="w-1/2 flex flex-col items-center border-solid border-0 border-l border-abacus">
                                    <div class="flex gap-1 items-center">
                                        <div class="xmr bg-center bg-no-repeat h-6 w-6"></div>
                                        Monero
                                    </div>
                                    <a href="/balance?crypto=xmr" class="bg-abacus hover:bg-abacus2 text-white rounded px-0.5 font-bold text-sm">0.00000000</a>

                                </div>
                            </div>

                        </div>

                    </div>





                    <span class="block 2xl:hidden w-full h-[1px] bg-white">&nbsp;</span>

                    <a class="text-black text-[13px] font-bold leading-[14px] px-[5px] py-[9px] uppercase   hover:bg-abacus2 2xl:hover:rounded-l-md h-full flex w-full 2xl:w-auto" href="/">
                        <div class="grid grid-cols-[30px,auto] gap-1 items-center"><i class="gg-home justify-self-center"></i> Home</div>
                    </a>


                    <div class="inline-block relative h-full hover:bg-abacus2 hover:w-full group 2xl:hover:w-[unset] w-full 2xl:w-auto">
                        <a class="text-black text-[13px] font-bold leading-[14px] px-[5px] py-[9px] uppercase   hover:bg-abacus2 h-full flex" href="/orders">
                            <div class="grid grid-cols-[30px,auto] gap-1 items-center"><i class="gg-tag justify-self-center"></i> Orders (0)</div> </a>

                        <div class="hidden 2xl:group-hover:block z-10 bg-[#5f6266] rounded-b-md absolute min-w-[220px] w-[unset]">
                            <a class="text-left text-white block text-[13px] font-bold leading-[14px] py-[8px] px-[14px] hover:bg-abacus2" href="/orders">Pending Orders</a>
                            <a class="text-left text-white block text-[13px] font-bold leading-[14px] py-[8px] px-[14px] hover:bg-abacus2" href="/orders?orders=accepted">Accepted Orders</a>
                            <a class="text-left text-white block text-[13px] font-bold leading-[14px] py-[8px] px-[14px] hover:bg-abacus2" href="/orders?orders=shipped">Shipped Orders</a>
                            <a class="text-left text-white block text-[13px] font-bold leading-[14px] py-[8px] px-[14px] hover:bg-abacus2" href="/orders?orders=finalized">Finalized Orders</a>
                            <a class="text-left text-white block text-[13px] font-bold leading-[14px] py-[8px] px-[14px] hover:bg-abacus2" href="/orders?orders=disputed">Disputed Orders</a>
                            <a class="text-left text-white block text-[13px] font-bold leading-[14px] py-[8px] px-[14px] hover:bg-abacus2" href="/orders?orders=canceled">Canceled Orders</a>
                            <a class="text-left text-white block text-[13px] font-bold leading-[14px] py-[8px] px-[14px] hover:bg-abacus2" href="/orders?orders=unpaid">Unpaid Orders</a>
                        </div>
                    </div>

                    <div class="inline-block relative h-full hover:bg-abacus2 hover:w-full group 2xl:hover:w-[unset] w-full 2xl:w-auto">
                        <a class="text-black text-[13px] font-bold leading-[14px] px-[5px] py-[9px] uppercase   hover:bg-abacus2 h-full flex" href="/messages">
                            <div class="grid grid-cols-[30px,auto] gap-1 items-center"><i class="gg-inbox justify-self-center"></i> Messages (0)</div></a>
                        <div class="hidden 2xl:group-hover:block z-10 bg-[#5f6266] rounded-b-md absolute min-w-[220px] w-[unset]">
                            <a class="text-left text-white block text-[13px] font-bold leading-[14px] py-[8px] px-[14px] hover:bg-abacus2" href="/messages">Conversations (0)</a>
                            <a class="text-left text-white block text-[13px] font-bold leading-[14px] py-[8px] px-[14px] hover:bg-abacus2" href="/chats">Orders (0)</a>
                            <a class="text-left text-white block text-[13px] font-bold leading-[14px] py-[8px] px-[14px] hover:bg-abacus2" href="/messages?folderid=2">Trash</a>
                            <a class="text-left text-white block text-[13px] font-bold leading-[14px] py-[8px] px-[14px] hover:bg-abacus2" href="/invitations">Invitations (0)</a>
                        </div>
                    </div>


                    <a class="text-black text-[13px] font-bold leading-[14px] px-[5px] py-[9px] uppercase   hover:bg-abacus2 h-full flex 2xl:hidden w-full 2xl:w-auto" href="/favorites"><div class="grid grid-cols-[30px,auto] gap-1 items-center"><i class="gg-collage justify-self-center"></i> WishList</div></a>




                    <a class="text-black text-[13px] font-bold leading-[14px] px-[5px] py-[9px] uppercase   hover:bg-abacus2 h-full flex w-full 2xl:w-auto" href="/balance"><div class="grid grid-cols-[30px,auto] gap-1 items-center"><i class="gg-credit-card justify-self-center"></i> Wallets</div></a>


                    <div class="inline-block relative h-full hover:bg-abacus2 hover:w-full group 2xl:hover:w-[unset] w-full 2xl:w-auto">
                        <a class="text-black text-[13px] font-bold leading-[14px] px-[5px] py-[9px] uppercase   hover:bg-abacus2 h-full flex" href="/editprofile">

                            <div class="grid grid-cols-[30px,auto] gap-1 items-center"><i class="gg-profile justify-self-center"></i>Profile</div></a>
                        <div class="hidden 2xl:group-hover:block z-10 bg-[#5f6266] rounded-b-md absolute min-w-[220px] w-[unset]">
                            <a class="text-left text-white block text-[13px] font-bold leading-[14px] py-[8px] px-[14px] hover:bg-abacus2" href="/profile/49c8fe6a4f452a024bcda0cf">View Feedback &amp; Profile</a>
                            <a class="text-left text-white block text-[13px] font-bold leading-[14px] py-[8px] px-[14px] hover:bg-abacus2" href="/editprofile">Edit Profile</a>
                        </div>
                    </div>


                    <span class="block 2xl:hidden w-full h-[1px] bg-white">&nbsp;</span>

                    <div class="inline-block relative h-full hover:bg-abacus2 hover:w-full group 2xl:hover:w-[unset] w-full 2xl:w-auto">
                        <a class="text-black text-[13px] font-bold leading-[14px] px-[5px] py-[9px] uppercase   hover:bg-abacus2 h-full flex" href="/support">
                            <div class="grid grid-cols-[30px,auto] gap-1 items-center"><i class="gg-support justify-self-center"></i>Support (0)</div></a>

                        <div class="hidden 2xl:group-hover:block z-10 bg-[#5f6266] rounded-b-md absolute min-w-[220px] w-[unset]">
                            <a class="text-left text-white block text-[13px] font-bold leading-[14px] py-[8px] px-[14px] hover:bg-abacus2" href="/support">Create Ticket</a>
                            <a class="text-left text-white block text-[13px] font-bold leading-[14px] py-[8px] px-[14px] hover:bg-abacus2" href="/tickets">My Tickets</a>
                            <a class="text-left text-white block text-[13px] font-bold leading-[14px] py-[8px] px-[14px] hover:bg-abacus2" href="/contact">View Contact Information</a>
                            <a target="_blank" class="text-left text-white block text-[13px] font-bold leading-[14px] py-[8px] px-[14px] hover:bg-abacus2" href="http://g66ol3eb5ujdckzqqfmjsbpdjufmjd5nsgdipvxmsh7rckzlhywlzlqd.onion/d/AbacusMarket">Subdread</a>
                        </div>
                    </div>


                    <a class="text-black text-[13px] font-bold leading-[14px] px-[5px] py-[9px] uppercase   hover:bg-abacus2 h-full flex 2xl:hidden w-full 2xl:w-auto" href="/tickets"><div class="grid grid-cols-[30px,auto] gap-1 items-center"><i class="gg-clipboard justify-self-center"></i>My Tickets (0)</div></a>

                    <a class="text-black text-[13px] font-bold leading-[14px] px-[5px] py-[9px] uppercase   hover:bg-abacus2 h-full flex 2xl:hidden w-full 2xl:w-auto" href="/affiliate"><div class="grid grid-cols-[30px,auto] gap-1 items-center"><i class="gg-link justify-self-center"></i>Affiliate</div></a>

                    <a class="text-black text-[13px] font-bold leading-[14px] px-[5px] py-[9px] uppercase   hover:bg-abacus2 h-full flex w-full 2xl:w-auto" href="http://abacusforafuhyaebalelwh3c2rojzjjkf7n2ubaxiaws5mxlcqjmdid.onion/" target="_blank"><div class="grid grid-cols-[30px,auto] gap-1 items-center"><i class="gg-list justify-self-center"></i>Forums</div></a>

                    <a class="text-black text-[13px] font-bold leading-[14px] px-[5px] py-[9px] uppercase   hover:bg-abacus2 h-full flex w-full 2xl:w-auto" href="/upgrade">
                        <div class="grid grid-cols-[30px,auto] gap-1 items-center"><i class="gg-user justify-self-center"></i>Start Selling</div>
                    </a>


                    <a class="text-black text-[13px] font-bold leading-[14px] px-[5px] 2xl:px-[3px] 3xl:px-[5px] py-[9px] uppercase   hover:bg-abacus2 h-full flex w-full 2xl:w-auto" href="/verify">
                        <div class="grid grid-cols-[30px,auto] gap-1 items-center"><i class="gg-check justify-self-center"></i>Verify URL</div>
                    </a>

                    <div class="inline-block relative h-full hover:bg-abacus2 hover:w-full group 2xl:hover:w-[unset] w-full 2xl:w-auto">
                        <a class="text-black text-[13px] font-bold leading-[14px] px-[5px] py-[9px] uppercase   hover:bg-abacus2 h-full flex" href="">
                            <div class="grid grid-cols-[30px,auto] gap-1 items-center"><i class="gg-band-aid justify-self-center"></i>Harm Reduction</div>
                        </a>

                        <div class="hidden 2xl:group-hover:block z-10 bg-[#5f6266] rounded-b-md absolute min-w-[220px] w-[unset]">
                            <a target="_blank" class="text-left text-white block text-[13px] font-bold leading-[14px] py-[8px] px-[14px] hover:bg-abacus2" href="/DrugUsersBible-LowRes.pdf">Drug Users Bible</a>
                            <a target="_blank" class="text-left text-white block text-[13px] font-bold leading-[14px] py-[8px] px-[14px] hover:bg-abacus2" href="http://aegisafeup23objkc3tcepv4kbhpgt3j5ksba72jvmpo4khggpr7sbid.onion/">Aegis</a>
                        </div>
                    </div>

                    <a class="text-abacus text-[13px] font-bold leading-[14px] px-[5px] py-[9px] uppercase   hover:bg-abacus2 h-full flex 2xl:hidden bg-white hover:text-white w-full 2xl:w-auto" href="/logout"><div class="grid grid-cols-[30px,auto] gap-1 items-center"><i class="gg-log-off justify-self-center"></i>Log Out</div></a>

                </div>


                <div class="flex items-center gap-1 relative">

                    <!-- theme switch and pop to save var -->

                    <label for="theme" class="themelb rounded-full hover:bg-white text-white hover:text-abacus p-2">
                        <i class="gg-moon"></i>
                        <i class="gg-sun !hidden"></i>
                    </label>

                    <style>
                        #theme ~ * #pop-theme {
                            display: none !important;
                        }
                        #theme:checked ~ * #pop-theme {
                            display: flex !important;
                        }
                    </style>

                    <form id="pop-theme" class="anim anim-fadeIn absolute p-2 right-[50px] border-solid border-abacus2 flex-col items-center w-32 rounded-md bg-white text-abacus top-1/2 -translate-y-1/2 z-50">
                        <b class="text-xs">Keep change?</b>
                        <div class="flex gap-2 justify-center">
                            <a href="/?light_mode=off" class="bg-abacus text-white border-0 px-2 rounded py-1 text-xs">Yes</a>

                            <label class="bg-abacus text-white border-0 px-2 rounded py-1 text-xs" for="theme">No</label>
                        </div>

                    </form>



                    <a class="group bg-red-600 rounded-full hover:!bg-red-800 h-9 w-9 mr-2 hidden 2xl:flex items-center justify-center float-right" href="/notifications">
                        <div class="absolute right-2 mt-16 w-max rounded bg-red-600 text-white px-2 py-1 opacity-0 group-hover:opacity-100 z-20">1 new notifications</div>
                        <div class="animate-wiggle"><i class="gg-bell text-white text-xl mb-1"></i></div>
                    </a>




                </div>
            </div>


            <!-- LOGO & TOGGLE MOBILE -->
            <div class="flex flex-row-reverse items-center justify-between px-4 2xl:hidden">
                <a href="/" class="flex items-center">
                    <div class="bg-center object-scale-down bg-contain bg-no-repeat logo-mobile-nav" style="width:226px; height: 51px;"></div>
                </a>

                <label for="nav" class="text-white h-[30px] border-solid border-transparent hover-border-white hover:bg-white hover:text-abacus flex items-center justify-center rounded">
                    <i class="gg-menu"></i>&nbsp;Menu
                </label>
            </div>

        </header>





        <!-- CURRENCIES PRICES MOBILE -->

        <div class="currencies grid 2xl:hidden overflow-x-hidden z-20 grid-rows-1 grid-cols-1 bg-abacus px-0 py-0.5 rounded-md text-center w-full self-end h-auto mb-2">
            <div class="text-white w-full text-center flex justify-around relative" style="font-size:11px;">
                <span class="text-white text-center"><span class="green">▲</span>USD 92,674.82</span>


                <span class="text-white text-center"><span class="green">▲</span>CAD 133,159.61</span>


                <span class="text-white text-center"><span class="green">▲</span>EUR 88,660.69</span>


                <span class="text-white text-center"><span class="green">▲</span>AUD 148,510.90</span>


                <span class="text-white text-center"><span class="green">▲</span>GBP 73,508.22</span>




            </div>
            <div class="text-white w-full text-center flex justify-around relative" style="font-size:11px;">
                <span class="text-white text-center"><span class="green">▲</span>USD 92,674.82</span>


                <span class="text-white text-center"><span class="green">▲</span>CAD 133,159.61</span>


                <span class="text-white text-center"><span class="green">▲</span>EUR 88,660.69</span>


                <span class="text-white text-center"><span class="green">▲</span>AUD 148,510.90</span>


                <span class="text-white text-center"><span class="green">▲</span>GBP 73,508.22</span>




            </div>
        </div>





        <div class="bg-white relative border-solid border-[1px] border-border rounded-md w-full">

            <div class="mx-auto min-h-[calc(100vh-230px)] p-4 flex flex-wrap gap-2">

                <!-- navigation -->

                <div class="flex items-center w-full hover:!border-abacus2 border-solid border-[1px] border-border rounded-md h-[38px] py-1">
                    <a class="text-abacus text-sm font-bold px-2 py3 flex items-center" href="/">
                        <i class="gg-home"></i>
                    </a>

                    <i class="gg-chevron-right text-abacus opacity-30"></i>

                    <a class="text-abacus hover:text-white hover:bg-abacus rounded text-sm font-bold px-2 py3" href="/">Home</a>

                    <i class="gg-chevron-right text-abacus opacity-30"></i>

                </div>



                <!-- profile user search categories and advanced search -->



                <div class="w-full 2xl:w-[270px] flex flex-col items-center justify-start mx-2">


                    <!-- user -->

                    <div class="bg-white border-solid border-border border-[1px] rounded-md p-2 hover:!border-abacus2 w-full flex flex-wrap mb-2 ">


                        <div class="w-[85px] flex" style="width: 85px !important;">



                            <a class="m-auto" href="/profile/49c8fe6a4f452a024bcda0cf" target="_blank">
                                <div class="picture picture-user w-[80px] !h-[80px] rounded-md"></div>
                            </a>

                        </div>


                        <div class="w-[calc(100%-85px)] p-1 flex flex-col items-center">



                            <div class="text-xs text-left w-full text-abacus border-solid border-0 border-b-[1px] border-abacus py-1">
                                <a class="bstd w-full text-sm font-bold text-abacus rounded py-0.5" href="/profile/49c8fe6a4f452a024bcda0cf">pussycat (0)

                                    <i class="px-0.5 rounded" style="background-color:#fff; font-size: 12px; color: green;">100%</i>
                                </a>

                                <div class="font-bold">Trust Level:<span class="ml-2 rounded bg-lvl1 px-2 py-[1px] text-white">1</span></div>

                            </div>






                            <a href="/notifications" class="group bg-red-600 hover:!bg-abacus2 hover:!text-white font-bold rounded py-2 w-full text-center my-1">
                                <div class="flex items-center text-white justify-center gap-1 w-full  group-hover:text-white">
                                    <div class="scale-[90%]"><i class="gg-bell animate-wiggle"></i></div>Notifications (1)</div></a>








                            <div class="flex items-center w-full justify-evenly text-xs">

                                <!-- btc -->
                                <div class="w-1/2 flex flex-col items-center py-[1px]">
                                    <div class="flex gap-1 items-center text-abacus">
                                        <div class="btc bg-center bg-no-repeat h-6 w-[17px]"></div>

                                        <!-- btc icon dark theme -->

                                        <div class="btc-icon mb-0.5 hidden rounded-full bg-white" style="width: 24px; height: 24px; margin-left: 3px;background-color: #fff !important;" alt="BTC"></div>
                                        Bitcoin
                                    </div>
                                    <a href="/balance" class="bg-abacus-lightblue hover:bg-abacus text-white rounded px-0.5 font-bold text-[11px]">0.00000000</a>
                                </div>
                                <!-- xmr -->
                                <div class="w-1/2 flex flex-col items-center border-solid border-0 border-l border-abacus">
                                    <div class="flex gap-1 items-center text-abacus">
                                        <div class="xmr bg-center bg-no-repeat h-6 w-6"></div>

                                        <!-- xmr icon dark theme -->

                                        <div class="xmr-icon mb-0.5 hidden rounded-full bg-white" style="width: 24px; height: 24px; margin-left: 3px;background-color: #fff !important;" alt="XMR"></div>
                                        Monero
                                    </div>
                                    <a href="/balance?crypto=xmr" class="bg-abacus2 hover:bg-abacus text-white rounded px-0.5 font-bold text-[11px]">0.00000000</a>
                                </div>
                            </div>

                        </div>






                        <div class="grid grid-cols-2 items-center justify-center gap-0.5 w-full border-solid border-0 border-t border-abacus pt-1.5">


                            <!-- buyers -->


                            <a href="/orders" class="h-8 text abacus hover:bg-abacus hover:text-white border-solid border border-abacus2 font-bold rounded py-2 px-1 w-full text-center flex items-center justify-center gap-3">
                                <div class="scale-[90%]"><i class="gg-tag"></i></div>My Orders
                            </a>

                            <a href="/favorites" class="h-8 text abacus hover:bg-abacus hover:text-white border-solid border border-abacus2 font-bold rounded py-2 px-1 w-full text-center flex items-center justify-center gap-3">
                                <div class="scale-[90%]"><i class="gg-heart"></i></div>My Favorites
                            </a>




                            <a href="/editprofile" class="h-8 text abacus hover:bg-abacus hover:text-white border-solid border border-abacus2  font-bold rounded py-2 px-1 w-full text-center flex items-center justify-center gap-3">
                                <div class="scale-[90%]"><i class="gg-profile"></i></div>My Settings
                            </a>

                            <a href="/logout" class="h-8 text abacus hover:bg-abacus hover:text-white border-solid border border-abacus2 font-bold rounded py-2 px-1 w-full text-center flex items-center justify-center gap-3">
                                <div class="scale-[90%]"><i class="gg-log-out"></i></div>Log Out
                            </a>

                        </div>









                        <!-- search settings -->

                        <input type="checkbox" name="set" id="set" class="absolute peer hidden">

                        <form id="set-s" class="infobox hover:!border-abacus2 !w-full mb-2 mx-auto h-[fit-content]" action="" method="post" style="border: 0px;padding: 0px;margin-top: 10px;">

                            <label for="set" class="infoboxheader infoboxheaderstats ">
                                <h1 class="infobox flex items-center justify-start 2xl:justify-center bg-abacus2 hover:bg-abacus !text-white !rounded-md !border-none py-2 !px-5">
                                    <i class="gg-options mr-3"></i>
                                    My Abacus
                                    <i class="gg-math-plus ml-3"></i>
                                </h1>
                            </label>

                            <div id="set-cont" class=" px-0.5 py-1 !text-abacus hidden flex-col">
                                <div class="text-xs text-gray-500 my-1 italic text-center" style="margin-top: 2px; margin-bottom: 8px;">Customize Abacus to your needs. Lot of more options coming soon</div>
                                <div class="col-span-2 flex items-center justify-start" style="margin-top: 10px;margin-bottom: 10px;">
                                    <h3 class="std">My Fiat Currency:&nbsp; </h3>

                                    <select name="da_currencyid" class="border-solid mr-2 rounded-md border-[1px] border-border2 px-2 py-[4px] hover:border-abacus2 text-abacus w-max bg-white">
                                        <option value="USD" selected="selected">USD</option>
                                        <option value="AUD">AUD</option>
                                        <option value="NZD">NZD</option>
                                        <option value="CAD">CAD</option>
                                        <option value="CHF">CHF</option>
                                        <option value="CNY">CNY</option>
                                        <option value="DKK">DKK</option>
                                        <option value="EUR">EUR</option>
                                        <option value="GBP">GBP</option>
                                        <option value="HKD">HKD</option>
                                        <option value="INR">INR</option>
                                        <option value="JPY">JPY</option>
                                        <option value="PLN">PLN</option>
                                        <option value="RUB">RUB</option>
                                        <option value="SEK">SEK</option>
                                        <option value="NOK">NOK</option>
                                        <option value="RON">RON</option>
                                        <option value="BRL">BRL</option>
                                        <option value="TRY">TRY</option>
                                        <option value="HUF">HUF</option>
                                        <option value="CZK">CZK</option>
                                        <option value="MXN">MXN</option>
                                        <option value="IDR">IDR</option>
                                    </select>
                                </div>

                                <h3 class="std">Show only items delivered to:</h3>
                                <select name="sd_tocountryid" class="std">
                                    <option value="-1" selected="">Any</option>


                                    <option value="1">Worldwide</option>
                                    <option value="331">* Australia</option>
                                    <option value="227">* United States</option>
                                    <option value="209">* Canada</option>
                                    <option value="285">* United Kingdom</option>
                                    <option value="257">* Germany</option>


                                    <option value="2">--- North America</option>

                                    <option value="205">Antigua and Barbuda</option>

                                    <option value="206">Bahamas</option>

                                    <option value="207">Barbados</option>

                                    <option value="208">Belize</option>

                                    <option value="209">Canada</option>

                                    <option value="210">Costa Rica</option>

                                    <option value="211">Cuba</option>

                                    <option value="212">Dominica</option>

                                    <option value="213">Dominican Republic</option>

                                    <option value="214">El Salvador</option>

                                    <option value="215">Grenada</option>

                                    <option value="216">Guatemala</option>

                                    <option value="217">Haiti</option>

                                    <option value="218">Honduras</option>

                                    <option value="219">Jamaica</option>

                                    <option value="220">Mexico</option>

                                    <option value="221">Nicaragua</option>

                                    <option value="222">Panama</option>

                                    <option value="223">Saint Kitts and Nevis</option>

                                    <option value="224">Saint Lucia</option>

                                    <option value="225">Saint Vincent and the Grenadines</option>

                                    <option value="226">Trinidad and Tobago</option>

                                    <option value="227">United States</option>

                                    <option value="3">--- South America</option>

                                    <option value="228">Argentina</option>

                                    <option value="229">Bolivia</option>

                                    <option value="230">Brazil</option>

                                    <option value="231">Chile</option>

                                    <option value="232">Colombia</option>

                                    <option value="233">Ecuador</option>

                                    <option value="234">Guyana</option>

                                    <option value="235">Paraguay</option>

                                    <option value="236">Peru</option>

                                    <option value="237">Suriname</option>

                                    <option value="238">Uruguay</option>

                                    <option value="239">Venezuela</option>

                                    <option value="4">--- Europe</option>

                                    <option value="240">Albania</option>

                                    <option value="241">Andorra</option>

                                    <option value="242">Armenia</option>

                                    <option value="243">Austria</option>

                                    <option value="244">Azerbaijan</option>

                                    <option value="245">Belarus</option>

                                    <option value="246">Belgium</option>

                                    <option value="247">Bosnia and Herzegovina</option>

                                    <option value="248">Bulgaria</option>

                                    <option value="249">Croatia</option>

                                    <option value="250">Cyprus</option>

                                    <option value="251">Czech Republic</option>

                                    <option value="252">Denmark</option>

                                    <option value="253">Estonia</option>

                                    <option value="254">Finland</option>

                                    <option value="255">France</option>

                                    <option value="256">Georgia</option>

                                    <option value="257">Germany</option>

                                    <option value="258">Greece</option>

                                    <option value="259">Hungary</option>

                                    <option value="260">Iceland</option>

                                    <option value="261">Ireland</option>

                                    <option value="262">Italy</option>

                                    <option value="263">Latvia</option>

                                    <option value="264">Liechtenstein</option>

                                    <option value="265">Lithuania</option>

                                    <option value="266">Luxembourg</option>

                                    <option value="267">Macedonia</option>

                                    <option value="268">Malta</option>

                                    <option value="269">Moldova</option>

                                    <option value="270">Monaco</option>

                                    <option value="271">Montenegro</option>

                                    <option value="272">Netherlands</option>

                                    <option value="273">Norway</option>

                                    <option value="274">Poland</option>

                                    <option value="275">Portugal</option>

                                    <option value="276">Romania</option>

                                    <option value="277">San Marino</option>

                                    <option value="278">Serbia</option>

                                    <option value="279">Slovakia</option>

                                    <option value="280">Slovenia</option>

                                    <option value="281">Spain</option>

                                    <option value="282">Sweden</option>

                                    <option value="283">Switzerland</option>

                                    <option value="284">Ukraine</option>

                                    <option value="285">United Kingdom</option>

                                    <option value="286">Vatican City</option>

                                    <option value="5">--- Asia</option>

                                    <option value="287">Afghanistan</option>

                                    <option value="288">Bahrain</option>

                                    <option value="289">Bangladesh</option>

                                    <option value="290">Bhutan</option>

                                    <option value="291">Brunei</option>

                                    <option value="292">Burma (Myanmar)</option>

                                    <option value="293">Cambodia</option>

                                    <option value="294">China</option>

                                    <option value="295">East Timor</option>

                                    <option value="296">India</option>

                                    <option value="297">Indonesia</option>

                                    <option value="298">Iran</option>

                                    <option value="299">Iraq</option>

                                    <option value="300">Israel</option>

                                    <option value="301">Japan</option>

                                    <option value="302">Jordan</option>

                                    <option value="303">Kazakhstan</option>

                                    <option value="304">Korea, North</option>

                                    <option value="305">Korea, South</option>

                                    <option value="306">Kuwait</option>

                                    <option value="307">Kyrgyzstan</option>

                                    <option value="308">Laos</option>

                                    <option value="309">Lebanon</option>

                                    <option value="310">Malaysia</option>

                                    <option value="311">Maldives</option>

                                    <option value="312">Mongolia</option>

                                    <option value="313">Nepal</option>

                                    <option value="314">Oman</option>

                                    <option value="315">Pakistan</option>

                                    <option value="316">Philippines</option>

                                    <option value="317">Qatar</option>

                                    <option value="318">Russia</option>

                                    <option value="319">Saudi Arabia</option>

                                    <option value="320">Singapore</option>

                                    <option value="321">Sri Lanka</option>

                                    <option value="322">Syria</option>

                                    <option value="323">Tajikistan</option>

                                    <option value="324">Thailand</option>

                                    <option value="325">Turkey</option>

                                    <option value="326">Turkmenistan</option>

                                    <option value="327">United Arab Emirates</option>

                                    <option value="328">Uzbekistan</option>

                                    <option value="329">Vietnam</option>

                                    <option value="330">Yemen</option>

                                    <option value="6">--- Oceania</option>

                                    <option value="331">Australia</option>

                                    <option value="332">Fiji</option>

                                    <option value="333">Kiribati</option>

                                    <option value="334">Marshall Islands</option>

                                    <option value="335">Micronesia</option>

                                    <option value="336">Nauru</option>

                                    <option value="337">New Zealand</option>

                                    <option value="338">Palau</option>

                                    <option value="339">Papua New Guinea</option>

                                    <option value="340">Samoa</option>

                                    <option value="341">Solomon Islands</option>

                                    <option value="342">Tonga</option>

                                    <option value="343">Tuvalu</option>

                                    <option value="344">Vanuatu</option>

                                    <option value="7">--- Africa</option>

                                    <option value="345">Algeria</option>

                                    <option value="346">Angola</option>

                                    <option value="347">Benin</option>

                                    <option value="348">Botswana</option>

                                    <option value="349">Burkina</option>

                                    <option value="350">Burundi</option>

                                    <option value="351">Cameroon</option>

                                    <option value="352">Cape Verde</option>

                                    <option value="353">Central African Republic</option>

                                    <option value="354">Chad</option>

                                    <option value="355">Comoros</option>

                                    <option value="356">Congo</option>

                                    <option value="357">Djibouti</option>

                                    <option value="358">Egypt</option>

                                    <option value="359">Equatorial Guinea</option>

                                    <option value="360">Eritrea</option>

                                    <option value="361">Ethiopia</option>

                                    <option value="362">Gabon</option>

                                    <option value="363">Gambia</option>

                                    <option value="364">Ghana</option>

                                    <option value="365">Guinea</option>

                                    <option value="366">Guinea-Bissau</option>

                                    <option value="367">Ivory Coast</option>

                                    <option value="368">Kenya</option>

                                    <option value="369">Lesotho</option>

                                    <option value="370">Liberia</option>

                                    <option value="371">Libya</option>

                                    <option value="372">Madagascar</option>

                                    <option value="373">Malawi</option>

                                    <option value="374">Mali</option>

                                    <option value="375">Mauritania</option>

                                    <option value="376">Mauritius</option>

                                    <option value="377">Morocco</option>

                                    <option value="378">Mozambique</option>

                                    <option value="379">Namibia</option>

                                    <option value="380">Niger</option>

                                    <option value="381">Nigeria</option>

                                    <option value="382">Rwanda</option>

                                    <option value="383">Sao Tome and Principe</option>

                                    <option value="384">Senegal</option>

                                    <option value="385">Seychelles</option>

                                    <option value="386">Sierra Leone</option>

                                    <option value="387">Somalia</option>

                                    <option value="388">South Africa</option>

                                    <option value="389">South Sudan</option>

                                    <option value="390">Sudan</option>

                                    <option value="391">Swaziland</option>

                                    <option value="392">Tanzania</option>

                                    <option value="393">Togo</option>

                                    <option value="394">Tunisia</option>

                                    <option value="395">Uganda</option>

                                    <option value="396">Zambia</option>

                                    <option value="397">Zimbabwe</option>

                                </select>

                                <h3 class="std">Show only items shipped from:</h3>
                                <select name="sd_countryid" class="std">
                                    <option value="-1" selected="">Any</option>

                                    <option value="1">Worldwide</option>
                                    <option value="331">* Australia</option>
                                    <option value="227">* United States</option>
                                    <option value="209">* Canada</option>
                                    <option value="285">* United Kingdom</option>
                                    <option value="257">* Germany</option>


                                    <option value="2">--- North America</option>

                                    <option value="205">Antigua and Barbuda</option>

                                    <option value="206">Bahamas</option>

                                    <option value="207">Barbados</option>

                                    <option value="208">Belize</option>

                                    <option value="209">Canada</option>

                                    <option value="210">Costa Rica</option>

                                    <option value="211">Cuba</option>

                                    <option value="212">Dominica</option>

                                    <option value="213">Dominican Republic</option>

                                    <option value="214">El Salvador</option>

                                    <option value="215">Grenada</option>

                                    <option value="216">Guatemala</option>

                                    <option value="217">Haiti</option>

                                    <option value="218">Honduras</option>

                                    <option value="219">Jamaica</option>

                                    <option value="220">Mexico</option>

                                    <option value="221">Nicaragua</option>

                                    <option value="222">Panama</option>

                                    <option value="223">Saint Kitts and Nevis</option>

                                    <option value="224">Saint Lucia</option>

                                    <option value="225">Saint Vincent and the Grenadines</option>

                                    <option value="226">Trinidad and Tobago</option>

                                    <option value="227">United States</option>

                                    <option value="3">--- South America</option>

                                    <option value="228">Argentina</option>

                                    <option value="229">Bolivia</option>

                                    <option value="230">Brazil</option>

                                    <option value="231">Chile</option>

                                    <option value="232">Colombia</option>

                                    <option value="233">Ecuador</option>

                                    <option value="234">Guyana</option>

                                    <option value="235">Paraguay</option>

                                    <option value="236">Peru</option>

                                    <option value="237">Suriname</option>

                                    <option value="238">Uruguay</option>

                                    <option value="239">Venezuela</option>

                                    <option value="4">--- Europe</option>

                                    <option value="240">Albania</option>

                                    <option value="241">Andorra</option>

                                    <option value="242">Armenia</option>

                                    <option value="243">Austria</option>

                                    <option value="244">Azerbaijan</option>

                                    <option value="245">Belarus</option>

                                    <option value="246">Belgium</option>

                                    <option value="247">Bosnia and Herzegovina</option>

                                    <option value="248">Bulgaria</option>

                                    <option value="249">Croatia</option>

                                    <option value="250">Cyprus</option>

                                    <option value="251">Czech Republic</option>

                                    <option value="252">Denmark</option>

                                    <option value="253">Estonia</option>

                                    <option value="254">Finland</option>

                                    <option value="255">France</option>

                                    <option value="256">Georgia</option>

                                    <option value="257">Germany</option>

                                    <option value="258">Greece</option>

                                    <option value="259">Hungary</option>

                                    <option value="260">Iceland</option>

                                    <option value="261">Ireland</option>

                                    <option value="262">Italy</option>

                                    <option value="263">Latvia</option>

                                    <option value="264">Liechtenstein</option>

                                    <option value="265">Lithuania</option>

                                    <option value="266">Luxembourg</option>

                                    <option value="267">Macedonia</option>

                                    <option value="268">Malta</option>

                                    <option value="269">Moldova</option>

                                    <option value="270">Monaco</option>

                                    <option value="271">Montenegro</option>

                                    <option value="272">Netherlands</option>

                                    <option value="273">Norway</option>

                                    <option value="274">Poland</option>

                                    <option value="275">Portugal</option>

                                    <option value="276">Romania</option>

                                    <option value="277">San Marino</option>

                                    <option value="278">Serbia</option>

                                    <option value="279">Slovakia</option>

                                    <option value="280">Slovenia</option>

                                    <option value="281">Spain</option>

                                    <option value="282">Sweden</option>

                                    <option value="283">Switzerland</option>

                                    <option value="284">Ukraine</option>

                                    <option value="285">United Kingdom</option>

                                    <option value="286">Vatican City</option>

                                    <option value="5">--- Asia</option>

                                    <option value="287">Afghanistan</option>

                                    <option value="288">Bahrain</option>

                                    <option value="289">Bangladesh</option>

                                    <option value="290">Bhutan</option>

                                    <option value="291">Brunei</option>

                                    <option value="292">Burma (Myanmar)</option>

                                    <option value="293">Cambodia</option>

                                    <option value="294">China</option>

                                    <option value="295">East Timor</option>

                                    <option value="296">India</option>

                                    <option value="297">Indonesia</option>

                                    <option value="298">Iran</option>

                                    <option value="299">Iraq</option>

                                    <option value="300">Israel</option>

                                    <option value="301">Japan</option>

                                    <option value="302">Jordan</option>

                                    <option value="303">Kazakhstan</option>

                                    <option value="304">Korea, North</option>

                                    <option value="305">Korea, South</option>

                                    <option value="306">Kuwait</option>

                                    <option value="307">Kyrgyzstan</option>

                                    <option value="308">Laos</option>

                                    <option value="309">Lebanon</option>

                                    <option value="310">Malaysia</option>

                                    <option value="311">Maldives</option>

                                    <option value="312">Mongolia</option>

                                    <option value="313">Nepal</option>

                                    <option value="314">Oman</option>

                                    <option value="315">Pakistan</option>

                                    <option value="316">Philippines</option>

                                    <option value="317">Qatar</option>

                                    <option value="318">Russia</option>

                                    <option value="319">Saudi Arabia</option>

                                    <option value="320">Singapore</option>

                                    <option value="321">Sri Lanka</option>

                                    <option value="322">Syria</option>

                                    <option value="323">Tajikistan</option>

                                    <option value="324">Thailand</option>

                                    <option value="325">Turkey</option>

                                    <option value="326">Turkmenistan</option>

                                    <option value="327">United Arab Emirates</option>

                                    <option value="328">Uzbekistan</option>

                                    <option value="329">Vietnam</option>

                                    <option value="330">Yemen</option>

                                    <option value="6">--- Oceania</option>

                                    <option value="331">Australia</option>

                                    <option value="332">Fiji</option>

                                    <option value="333">Kiribati</option>

                                    <option value="334">Marshall Islands</option>

                                    <option value="335">Micronesia</option>

                                    <option value="336">Nauru</option>

                                    <option value="337">New Zealand</option>

                                    <option value="338">Palau</option>

                                    <option value="339">Papua New Guinea</option>

                                    <option value="340">Samoa</option>

                                    <option value="341">Solomon Islands</option>

                                    <option value="342">Tonga</option>

                                    <option value="343">Tuvalu</option>

                                    <option value="344">Vanuatu</option>

                                    <option value="7">--- Africa</option>

                                    <option value="345">Algeria</option>

                                    <option value="346">Angola</option>

                                    <option value="347">Benin</option>

                                    <option value="348">Botswana</option>

                                    <option value="349">Burkina</option>

                                    <option value="350">Burundi</option>

                                    <option value="351">Cameroon</option>

                                    <option value="352">Cape Verde</option>

                                    <option value="353">Central African Republic</option>

                                    <option value="354">Chad</option>

                                    <option value="355">Comoros</option>

                                    <option value="356">Congo</option>

                                    <option value="357">Djibouti</option>

                                    <option value="358">Egypt</option>

                                    <option value="359">Equatorial Guinea</option>

                                    <option value="360">Eritrea</option>

                                    <option value="361">Ethiopia</option>

                                    <option value="362">Gabon</option>

                                    <option value="363">Gambia</option>

                                    <option value="364">Ghana</option>

                                    <option value="365">Guinea</option>

                                    <option value="366">Guinea-Bissau</option>

                                    <option value="367">Ivory Coast</option>

                                    <option value="368">Kenya</option>

                                    <option value="369">Lesotho</option>

                                    <option value="370">Liberia</option>

                                    <option value="371">Libya</option>

                                    <option value="372">Madagascar</option>

                                    <option value="373">Malawi</option>

                                    <option value="374">Mali</option>

                                    <option value="375">Mauritania</option>

                                    <option value="376">Mauritius</option>

                                    <option value="377">Morocco</option>

                                    <option value="378">Mozambique</option>

                                    <option value="379">Namibia</option>

                                    <option value="380">Niger</option>

                                    <option value="381">Nigeria</option>

                                    <option value="382">Rwanda</option>

                                    <option value="383">Sao Tome and Principe</option>

                                    <option value="384">Senegal</option>

                                    <option value="385">Seychelles</option>

                                    <option value="386">Sierra Leone</option>

                                    <option value="387">Somalia</option>

                                    <option value="388">South Africa</option>

                                    <option value="389">South Sudan</option>

                                    <option value="390">Sudan</option>

                                    <option value="391">Swaziland</option>

                                    <option value="392">Tanzania</option>

                                    <option value="393">Togo</option>

                                    <option value="394">Tunisia</option>

                                    <option value="395">Uganda</option>

                                    <option value="396">Zambia</option>

                                    <option value="397">Zimbabwe</option>
                                </select>


                                <h3 class="std flex" style="margin-top: 15px;">Hide items from unknown location?:
                                    <div class="text-abacus cursor-pointer peer rounded-full px-0.5 -mt-1 font-bold text-xs font-mono">(i)</div>
                                    <div class="scale-0 opacity-0 peer-hover:scale-100 peer-hover:opacity-100 absolute left-1/2 2xl:!left-auto -translate-x-1/2 2xl:!translate-x-px p-1 text-white pointer-events-none bg-abacus bg-opacity-90 rounded-md font-normal w-11/12 2xl:w-1/3 z-10">
                                        <div class="m-[1px] p-[2px] rounded-md border-solid border-[1px] border-white not-italic">Some vendors do not specify the country from where they ship their goods and they choose ''Worldwide'' as their shipping origin which is the same than not specifying the exact location from where they ship their items. By marking this box you can skip these items from your search results.</div>
                                    </div>
                                    <input name="sd_discardww" type="checkbox" value="1"></h3>




                                <div class="mx-auto flex gap-2 items-center" style="margin-top: 20px;margin-bottom: -10px;">
                                    <input id="search-btn" class="bstd !bg-abacus2 !border-none hover:bg-abacus mx-auto my-1" name="quick_settings" value="Save" type="submit"><p></p>
                                    <input type="hidden" name="csrf_token" value="8a196de12a3b6d5e.z5VOBHOpKw0AAy-kq0QAmS3fs-2ITniblB00o8AqOQI.i9QYST3fRmpid3yQzyBI4UqPnoDdHy73xl5G2rpEY3aL7xdyHshSP0JORA">
                                    <label for="set" class="bg-abacus2 hover:bg-abacus px-2 py-[.5px] block 2xl:hidden text-white text-sm font-bold w-max rounded">Close</label>
                                </div>

                            </div>



                        </form>


                        <!-- end search -->


                    </div><!-- search -->




                    <!-- WHATS NEW FOR ME -->

                    <div class="bg-white border-solid border-border border-[1px] rounded-md p-2 hover:!border-abacus2 w-full flex flex-col gap-0.5 items-center p-1 text-sm mb-2" style="max-height: 350px;">

                        <label class="infoboxheader infoboxheaderstats w-full">
                            <h1 class="infobox flex items-center justify-center bg-abacus !text-white !rounded-md !border-none py-1.5">
                                <i class="gg-layout-list mr-3"></i>WHAT'S NEW FOR ME?</h1>
                        </label>

                        <div class="w-full border-solid border-0 border-b border-abacus text-center text-xs pb-1">1 unread notifications <br>
                            <a href="/notifications" class="text-black px-1 font-bold underline">Click here to see all</a></div>

                        <div class="flex flex-col items-center gap-2 text-xs w-full py-1 overflow-y-auto pr-1">

                            <div class="bg-abacus bg-opacity-10 text-black w-full px-2 py-1 rounded hover:font-bold hover:underline border-border border-solid border hover:border-abacus group font-bold">
                                Welcome to Abacus Market, we will do our best to make it your home. You can proceed depositing funds or upgrading directly to vendor if you have a vendor account on an established market. Do not forget to subscribe to our subdread /d/AbacusMarket Regards.
                                <span class="text-gray-600 flex items-center justify-between font-bold">Dec 29, 2024 at 08:30<a href="/notifications?view=38aa71b4961e074f26728bcc" class=" items-center gap-1 hover:gap-0 flex bg-abacus2 hover:bg-abacus text-white rounded pl-2 ">
                                                                                                                       </a></span>
                            </div>


                        </div>

                    </div>

                    <!-- END WHATS NEW FOR ME -->




                    <!-- search quick -->
                    <form name="formHomesidebar" action="/search" method="get" class="w-full flex flex-col gap-2 mb-2">

                        <input type="checkbox" name="qck" id="qck" class="absolute peer hidden" style="margin-bottom: 15px;">

                        <div id="qck-s" class="infobox hover:!border-abacus2 !w-full my-0 mx-auto h-[fit-content]">

                            <label for="qck" class="infoboxheader infoboxheaderstats">
                                <h1 class="infobox flex items-center justify-start 2xl:justify-center !text-abacus !bg-white 2xl:!bg-abacus 2xl:!text-white !rounded-md !border-none py-2"><i class="gg-search mr-3"></i>Quick Search</h1>
                            </label>

                            <div id="qck-cont" class=" px-0.5 py-1 !text-abacus hidden 2xl:flex flex-col">

                                <input type="checkbox" name="adv" id="adv" class="absolute peer hidden">

                                <h3 class="std">Search Product:</h3>
                                <input class="std" name="s_terms" size="30" placeholder="What are you looking for?" type="text" style="border-radius: 6px; height: 25px;" value="">

                                <h3 class="std">Search Vendor:</h3>
                                <input class="std" name="s_sellername" size="30" placeholder="Type Vendor Username..." type="text" style="border-radius: 6px; height: 25px;" value="">


                                <h3 class="std flex">Sort by:

                                    <div class="text-abacus cursor-pointer peer rounded-full px-0.5 -mt-1 font-bold text-xs font-mono">(i)</div>
                                    <div class="scale-0 opacity-0 peer-hover:scale-100 peer-hover:opacity-100 absolute left-1/2 2xl:!left-auto -translate-x-1/2 2xl:!translate-x-px p-1 text-white pointer-events-none bg-abacus bg-opacity-90 rounded-md font-normal w-11/12 2xl:w-1/3 z-10">
                                        <div class="m-[1px] p-[2px] rounded-md border-solid border-[1px] border-white not-italic"><b>Most popular: </b>By default results are sorted based on number of sales and other criteria.<br><b>Best match:</b> You must use the best match filter if you wish to give priority to most accurate results based in the entered term.
                                        </div>
                                    </div>

                                </h3>
                                <select name="s_order" class="std">
                                    <option value="0" selected="selected">Most popular item</option>

                                    <option value="1">Best match</option>

                                    <option value="2">Most recent</option>

                                    <option value="3">Most ancient</option>

                                    <option value="4">Lowest price</option>

                                    <option value="5">Highest price</option>
                                </select>


                                <h3 class="hidden peer-checked:flex anim anim-fadeIn std">Ships to:</h3>
                                <select name="s_tocountryid" class="hidden peer-checked:flex anim anim-fadeIn std">
                                    <option value="-1" selected="">Any</option>


                                    <option value="1">Worldwide</option>
                                    <option value="331">* Australia</option>
                                    <option value="227">* United States</option>
                                    <option value="209">* Canada</option>
                                    <option value="285">* United Kingdom</option>
                                    <option value="257">* Germany</option>


                                    <option value="2">--- North America</option>

                                    <option value="205">Antigua and Barbuda</option>

                                    <option value="206">Bahamas</option>

                                    <option value="207">Barbados</option>

                                    <option value="208">Belize</option>

                                    <option value="209">Canada</option>

                                    <option value="210">Costa Rica</option>

                                    <option value="211">Cuba</option>

                                    <option value="212">Dominica</option>

                                    <option value="213">Dominican Republic</option>

                                    <option value="214">El Salvador</option>

                                    <option value="215">Grenada</option>

                                    <option value="216">Guatemala</option>

                                    <option value="217">Haiti</option>

                                    <option value="218">Honduras</option>

                                    <option value="219">Jamaica</option>

                                    <option value="220">Mexico</option>

                                    <option value="221">Nicaragua</option>

                                    <option value="222">Panama</option>

                                    <option value="223">Saint Kitts and Nevis</option>

                                    <option value="224">Saint Lucia</option>

                                    <option value="225">Saint Vincent and the Grenadines</option>

                                    <option value="226">Trinidad and Tobago</option>

                                    <option value="227">United States</option>

                                    <option value="3">--- South America</option>

                                    <option value="228">Argentina</option>

                                    <option value="229">Bolivia</option>

                                    <option value="230">Brazil</option>

                                    <option value="231">Chile</option>

                                    <option value="232">Colombia</option>

                                    <option value="233">Ecuador</option>

                                    <option value="234">Guyana</option>

                                    <option value="235">Paraguay</option>

                                    <option value="236">Peru</option>

                                    <option value="237">Suriname</option>

                                    <option value="238">Uruguay</option>

                                    <option value="239">Venezuela</option>

                                    <option value="4">--- Europe</option>

                                    <option value="240">Albania</option>

                                    <option value="241">Andorra</option>

                                    <option value="242">Armenia</option>

                                    <option value="243">Austria</option>

                                    <option value="244">Azerbaijan</option>

                                    <option value="245">Belarus</option>

                                    <option value="246">Belgium</option>

                                    <option value="247">Bosnia and Herzegovina</option>

                                    <option value="248">Bulgaria</option>

                                    <option value="249">Croatia</option>

                                    <option value="250">Cyprus</option>

                                    <option value="251">Czech Republic</option>

                                    <option value="252">Denmark</option>

                                    <option value="253">Estonia</option>

                                    <option value="254">Finland</option>

                                    <option value="255">France</option>

                                    <option value="256">Georgia</option>

                                    <option value="257">Germany</option>

                                    <option value="258">Greece</option>

                                    <option value="259">Hungary</option>

                                    <option value="260">Iceland</option>

                                    <option value="261">Ireland</option>

                                    <option value="262">Italy</option>

                                    <option value="263">Latvia</option>

                                    <option value="264">Liechtenstein</option>

                                    <option value="265">Lithuania</option>

                                    <option value="266">Luxembourg</option>

                                    <option value="267">Macedonia</option>

                                    <option value="268">Malta</option>

                                    <option value="269">Moldova</option>

                                    <option value="270">Monaco</option>

                                    <option value="271">Montenegro</option>

                                    <option value="272">Netherlands</option>

                                    <option value="273">Norway</option>

                                    <option value="274">Poland</option>

                                    <option value="275">Portugal</option>

                                    <option value="276">Romania</option>

                                    <option value="277">San Marino</option>

                                    <option value="278">Serbia</option>

                                    <option value="279">Slovakia</option>

                                    <option value="280">Slovenia</option>

                                    <option value="281">Spain</option>

                                    <option value="282">Sweden</option>

                                    <option value="283">Switzerland</option>

                                    <option value="284">Ukraine</option>

                                    <option value="285">United Kingdom</option>

                                    <option value="286">Vatican City</option>

                                    <option value="5">--- Asia</option>

                                    <option value="287">Afghanistan</option>

                                    <option value="288">Bahrain</option>

                                    <option value="289">Bangladesh</option>

                                    <option value="290">Bhutan</option>

                                    <option value="291">Brunei</option>

                                    <option value="292">Burma (Myanmar)</option>

                                    <option value="293">Cambodia</option>

                                    <option value="294">China</option>

                                    <option value="295">East Timor</option>

                                    <option value="296">India</option>

                                    <option value="297">Indonesia</option>

                                    <option value="298">Iran</option>

                                    <option value="299">Iraq</option>

                                    <option value="300">Israel</option>

                                    <option value="301">Japan</option>

                                    <option value="302">Jordan</option>

                                    <option value="303">Kazakhstan</option>

                                    <option value="304">Korea, North</option>

                                    <option value="305">Korea, South</option>

                                    <option value="306">Kuwait</option>

                                    <option value="307">Kyrgyzstan</option>

                                    <option value="308">Laos</option>

                                    <option value="309">Lebanon</option>

                                    <option value="310">Malaysia</option>

                                    <option value="311">Maldives</option>

                                    <option value="312">Mongolia</option>

                                    <option value="313">Nepal</option>

                                    <option value="314">Oman</option>

                                    <option value="315">Pakistan</option>

                                    <option value="316">Philippines</option>

                                    <option value="317">Qatar</option>

                                    <option value="318">Russia</option>

                                    <option value="319">Saudi Arabia</option>

                                    <option value="320">Singapore</option>

                                    <option value="321">Sri Lanka</option>

                                    <option value="322">Syria</option>

                                    <option value="323">Tajikistan</option>

                                    <option value="324">Thailand</option>

                                    <option value="325">Turkey</option>

                                    <option value="326">Turkmenistan</option>

                                    <option value="327">United Arab Emirates</option>

                                    <option value="328">Uzbekistan</option>

                                    <option value="329">Vietnam</option>

                                    <option value="330">Yemen</option>

                                    <option value="6">--- Oceania</option>

                                    <option value="331">Australia</option>

                                    <option value="332">Fiji</option>

                                    <option value="333">Kiribati</option>

                                    <option value="334">Marshall Islands</option>

                                    <option value="335">Micronesia</option>

                                    <option value="336">Nauru</option>

                                    <option value="337">New Zealand</option>

                                    <option value="338">Palau</option>

                                    <option value="339">Papua New Guinea</option>

                                    <option value="340">Samoa</option>

                                    <option value="341">Solomon Islands</option>

                                    <option value="342">Tonga</option>

                                    <option value="343">Tuvalu</option>

                                    <option value="344">Vanuatu</option>

                                    <option value="7">--- Africa</option>

                                    <option value="345">Algeria</option>

                                    <option value="346">Angola</option>

                                    <option value="347">Benin</option>

                                    <option value="348">Botswana</option>

                                    <option value="349">Burkina</option>

                                    <option value="350">Burundi</option>

                                    <option value="351">Cameroon</option>

                                    <option value="352">Cape Verde</option>

                                    <option value="353">Central African Republic</option>

                                    <option value="354">Chad</option>

                                    <option value="355">Comoros</option>

                                    <option value="356">Congo</option>

                                    <option value="357">Djibouti</option>

                                    <option value="358">Egypt</option>

                                    <option value="359">Equatorial Guinea</option>

                                    <option value="360">Eritrea</option>

                                    <option value="361">Ethiopia</option>

                                    <option value="362">Gabon</option>

                                    <option value="363">Gambia</option>

                                    <option value="364">Ghana</option>

                                    <option value="365">Guinea</option>

                                    <option value="366">Guinea-Bissau</option>

                                    <option value="367">Ivory Coast</option>

                                    <option value="368">Kenya</option>

                                    <option value="369">Lesotho</option>

                                    <option value="370">Liberia</option>

                                    <option value="371">Libya</option>

                                    <option value="372">Madagascar</option>

                                    <option value="373">Malawi</option>

                                    <option value="374">Mali</option>

                                    <option value="375">Mauritania</option>

                                    <option value="376">Mauritius</option>

                                    <option value="377">Morocco</option>

                                    <option value="378">Mozambique</option>

                                    <option value="379">Namibia</option>

                                    <option value="380">Niger</option>

                                    <option value="381">Nigeria</option>

                                    <option value="382">Rwanda</option>

                                    <option value="383">Sao Tome and Principe</option>

                                    <option value="384">Senegal</option>

                                    <option value="385">Seychelles</option>

                                    <option value="386">Sierra Leone</option>

                                    <option value="387">Somalia</option>

                                    <option value="388">South Africa</option>

                                    <option value="389">South Sudan</option>

                                    <option value="390">Sudan</option>

                                    <option value="391">Swaziland</option>

                                    <option value="392">Tanzania</option>

                                    <option value="393">Togo</option>

                                    <option value="394">Tunisia</option>

                                    <option value="395">Uganda</option>

                                    <option value="396">Zambia</option>

                                    <option value="397">Zimbabwe</option>
                                </select>

                                <h3 class="hidden peer-checked:flex anim anim-fadeIn std">Origin country:</h3>
                                <select name="s_countryid" class="hidden peer-checked:flex anim anim-fadeIn std">
                                    <option value="-1" selected="">Any</option>

                                    <option value="1">Worldwide</option>
                                    <option value="331">* Australia</option>
                                    <option value="227">* United States</option>
                                    <option value="209">* Canada</option>
                                    <option value="285">* United Kingdom</option>
                                    <option value="257">* Germany</option>


                                    <option value="2">--- North America</option>

                                    <option value="205">Antigua and Barbuda</option>

                                    <option value="206">Bahamas</option>

                                    <option value="207">Barbados</option>

                                    <option value="208">Belize</option>

                                    <option value="209">Canada</option>

                                    <option value="210">Costa Rica</option>

                                    <option value="211">Cuba</option>

                                    <option value="212">Dominica</option>

                                    <option value="213">Dominican Republic</option>

                                    <option value="214">El Salvador</option>

                                    <option value="215">Grenada</option>

                                    <option value="216">Guatemala</option>

                                    <option value="217">Haiti</option>

                                    <option value="218">Honduras</option>

                                    <option value="219">Jamaica</option>

                                    <option value="220">Mexico</option>

                                    <option value="221">Nicaragua</option>

                                    <option value="222">Panama</option>

                                    <option value="223">Saint Kitts and Nevis</option>

                                    <option value="224">Saint Lucia</option>

                                    <option value="225">Saint Vincent and the Grenadines</option>

                                    <option value="226">Trinidad and Tobago</option>

                                    <option value="227">United States</option>

                                    <option value="3">--- South America</option>

                                    <option value="228">Argentina</option>

                                    <option value="229">Bolivia</option>

                                    <option value="230">Brazil</option>

                                    <option value="231">Chile</option>

                                    <option value="232">Colombia</option>

                                    <option value="233">Ecuador</option>

                                    <option value="234">Guyana</option>

                                    <option value="235">Paraguay</option>

                                    <option value="236">Peru</option>

                                    <option value="237">Suriname</option>

                                    <option value="238">Uruguay</option>

                                    <option value="239">Venezuela</option>

                                    <option value="4">--- Europe</option>

                                    <option value="240">Albania</option>

                                    <option value="241">Andorra</option>

                                    <option value="242">Armenia</option>

                                    <option value="243">Austria</option>

                                    <option value="244">Azerbaijan</option>

                                    <option value="245">Belarus</option>

                                    <option value="246">Belgium</option>

                                    <option value="247">Bosnia and Herzegovina</option>

                                    <option value="248">Bulgaria</option>

                                    <option value="249">Croatia</option>

                                    <option value="250">Cyprus</option>

                                    <option value="251">Czech Republic</option>

                                    <option value="252">Denmark</option>

                                    <option value="253">Estonia</option>

                                    <option value="254">Finland</option>

                                    <option value="255">France</option>

                                    <option value="256">Georgia</option>

                                    <option value="257">Germany</option>

                                    <option value="258">Greece</option>

                                    <option value="259">Hungary</option>

                                    <option value="260">Iceland</option>

                                    <option value="261">Ireland</option>

                                    <option value="262">Italy</option>

                                    <option value="263">Latvia</option>

                                    <option value="264">Liechtenstein</option>

                                    <option value="265">Lithuania</option>

                                    <option value="266">Luxembourg</option>

                                    <option value="267">Macedonia</option>

                                    <option value="268">Malta</option>

                                    <option value="269">Moldova</option>

                                    <option value="270">Monaco</option>

                                    <option value="271">Montenegro</option>

                                    <option value="272">Netherlands</option>

                                    <option value="273">Norway</option>

                                    <option value="274">Poland</option>

                                    <option value="275">Portugal</option>

                                    <option value="276">Romania</option>

                                    <option value="277">San Marino</option>

                                    <option value="278">Serbia</option>

                                    <option value="279">Slovakia</option>

                                    <option value="280">Slovenia</option>

                                    <option value="281">Spain</option>

                                    <option value="282">Sweden</option>

                                    <option value="283">Switzerland</option>

                                    <option value="284">Ukraine</option>

                                    <option value="285">United Kingdom</option>

                                    <option value="286">Vatican City</option>

                                    <option value="5">--- Asia</option>

                                    <option value="287">Afghanistan</option>

                                    <option value="288">Bahrain</option>

                                    <option value="289">Bangladesh</option>

                                    <option value="290">Bhutan</option>

                                    <option value="291">Brunei</option>

                                    <option value="292">Burma (Myanmar)</option>

                                    <option value="293">Cambodia</option>

                                    <option value="294">China</option>

                                    <option value="295">East Timor</option>

                                    <option value="296">India</option>

                                    <option value="297">Indonesia</option>

                                    <option value="298">Iran</option>

                                    <option value="299">Iraq</option>

                                    <option value="300">Israel</option>

                                    <option value="301">Japan</option>

                                    <option value="302">Jordan</option>

                                    <option value="303">Kazakhstan</option>

                                    <option value="304">Korea, North</option>

                                    <option value="305">Korea, South</option>

                                    <option value="306">Kuwait</option>

                                    <option value="307">Kyrgyzstan</option>

                                    <option value="308">Laos</option>

                                    <option value="309">Lebanon</option>

                                    <option value="310">Malaysia</option>

                                    <option value="311">Maldives</option>

                                    <option value="312">Mongolia</option>

                                    <option value="313">Nepal</option>

                                    <option value="314">Oman</option>

                                    <option value="315">Pakistan</option>

                                    <option value="316">Philippines</option>

                                    <option value="317">Qatar</option>

                                    <option value="318">Russia</option>

                                    <option value="319">Saudi Arabia</option>

                                    <option value="320">Singapore</option>

                                    <option value="321">Sri Lanka</option>

                                    <option value="322">Syria</option>

                                    <option value="323">Tajikistan</option>

                                    <option value="324">Thailand</option>

                                    <option value="325">Turkey</option>

                                    <option value="326">Turkmenistan</option>

                                    <option value="327">United Arab Emirates</option>

                                    <option value="328">Uzbekistan</option>

                                    <option value="329">Vietnam</option>

                                    <option value="330">Yemen</option>

                                    <option value="6">--- Oceania</option>

                                    <option value="331">Australia</option>

                                    <option value="332">Fiji</option>

                                    <option value="333">Kiribati</option>

                                    <option value="334">Marshall Islands</option>

                                    <option value="335">Micronesia</option>

                                    <option value="336">Nauru</option>

                                    <option value="337">New Zealand</option>

                                    <option value="338">Palau</option>

                                    <option value="339">Papua New Guinea</option>

                                    <option value="340">Samoa</option>

                                    <option value="341">Solomon Islands</option>

                                    <option value="342">Tonga</option>

                                    <option value="343">Tuvalu</option>

                                    <option value="344">Vanuatu</option>

                                    <option value="7">--- Africa</option>

                                    <option value="345">Algeria</option>

                                    <option value="346">Angola</option>

                                    <option value="347">Benin</option>

                                    <option value="348">Botswana</option>

                                    <option value="349">Burkina</option>

                                    <option value="350">Burundi</option>

                                    <option value="351">Cameroon</option>

                                    <option value="352">Cape Verde</option>

                                    <option value="353">Central African Republic</option>

                                    <option value="354">Chad</option>

                                    <option value="355">Comoros</option>

                                    <option value="356">Congo</option>

                                    <option value="357">Djibouti</option>

                                    <option value="358">Egypt</option>

                                    <option value="359">Equatorial Guinea</option>

                                    <option value="360">Eritrea</option>

                                    <option value="361">Ethiopia</option>

                                    <option value="362">Gabon</option>

                                    <option value="363">Gambia</option>

                                    <option value="364">Ghana</option>

                                    <option value="365">Guinea</option>

                                    <option value="366">Guinea-Bissau</option>

                                    <option value="367">Ivory Coast</option>

                                    <option value="368">Kenya</option>

                                    <option value="369">Lesotho</option>

                                    <option value="370">Liberia</option>

                                    <option value="371">Libya</option>

                                    <option value="372">Madagascar</option>

                                    <option value="373">Malawi</option>

                                    <option value="374">Mali</option>

                                    <option value="375">Mauritania</option>

                                    <option value="376">Mauritius</option>

                                    <option value="377">Morocco</option>

                                    <option value="378">Mozambique</option>

                                    <option value="379">Namibia</option>

                                    <option value="380">Niger</option>

                                    <option value="381">Nigeria</option>

                                    <option value="382">Rwanda</option>

                                    <option value="383">Sao Tome and Principe</option>

                                    <option value="384">Senegal</option>

                                    <option value="385">Seychelles</option>

                                    <option value="386">Sierra Leone</option>

                                    <option value="387">Somalia</option>

                                    <option value="388">South Africa</option>

                                    <option value="389">South Sudan</option>

                                    <option value="390">Sudan</option>

                                    <option value="391">Swaziland</option>

                                    <option value="392">Tanzania</option>

                                    <option value="393">Togo</option>

                                    <option value="394">Tunisia</option>

                                    <option value="395">Uganda</option>

                                    <option value="396">Zambia</option>

                                    <option value="397">Zimbabwe</option>
                                </select>


                                <h3 class="hidden peer-checked:flex anim anim-fadeIn std flex">Discard origin Worldwide?:
                                    <div class="text-abacus cursor-pointer peer rounded-full px-0.5 -mt-1 font-bold text-xs font-mono">(i)</div>
                                    <div class="scale-0 opacity-0 peer-hover:scale-100 peer-hover:opacity-100 absolute left-1/2 2xl:!left-auto -translate-x-1/2 2xl:!translate-x-px p-1 text-white pointer-events-none bg-abacus bg-opacity-90 rounded-md font-normal w-11/12 2xl:w-1/3 z-10">
                                        <div class="m-[1px] p-[2px] rounded-md border-solid border-[1px] border-white not-italic">Some vendors do not specify the country from where they ship their goods and they choose ''Worldwide'' as their shipping origin. By marking this box you can skip these vendors from your search results.</div>
                                    </div>
                                    <input name="s_discardww" type="checkbox" value="1"></h3>


                                <h3 class="hidden peer-checked:flex anim anim-fadeIn std">By Category:</h3>
                                <select name="s_category" class="hidden peer-checked:flex anim anim-fadeIn std">

                                    <option>All</option>

                                    <option value="60792aab2b9afc46f066b529">Drugs &amp; Chemicals</option>
                                    <option value="a57acb011e82fe31ea5b8493">&nbsp;&nbsp;&nbsp;&nbsp;Benzos</option>
                                    <option value="607d0bdf688485a9ae59d7dd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pills</option>
                                    <option value="4ae60fd1377c6e87c12c6f0c">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Powder</option>
                                    <option value="44007a13d0f1dbc2bc007f3a">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;RC</option>
                                    <option value="252973d6359fad98502a5dd0">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Other</option>
                                    <option value="e88ee39c23db7ebfb5d581be">&nbsp;&nbsp;&nbsp;&nbsp;Cannabis &amp; Hashish</option>
                                    <option value="3494d026c3bcf57e3ec62d6e">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Buds &amp; Flowers</option>
                                    <option value="8f984cf7a02ee25880aedcfa">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Shake</option>
                                    <option value="6c1816a1a5a42654eff928e3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Concentrates</option>
                                    <option value="9b66a5126f06548921cf5a47">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hash</option>
                                    <option value="4ae717b40185fcb776302b5c">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Prerolls</option>
                                    <option value="ebde2caabf602f5c5e4f7897">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Edibles</option>
                                    <option value="5676785600c4c27d03843f26">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Topicals &amp; Others</option>
                                    <option value="c8b853a11384e15ec7db92a8">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Seeds</option>
                                    <option value="5f9314754f2fdb550b203d84">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Synthetic</option>
                                    <option value="5f1c232f71afee2bddfe76e9">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Cart</option>
                                    <option value="6b42cb0301794710c4277455">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Other</option>
                                    <option value="d9c3eb66627d1da608b19f30">&nbsp;&nbsp;&nbsp;&nbsp;Dissociatives</option>
                                    <option value="cf4e5423fb060eafede41c81">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Ketamine</option>
                                    <option value="d49237fb4830f373f178c53a">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MXE</option>
                                    <option value="cf787dadcaf005bfb8601b79">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;GHB</option>
                                    <option value="827ff78d82d47eff17202b74">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Other</option>
                                    <option value="748fb852dc76fb8c1ac1e525">&nbsp;&nbsp;&nbsp;&nbsp;Ecstasy</option>
                                    <option value="8a994f98835a08c7597e77ba">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pills</option>
                                    <option value="5789a88768a95c9e847d7e52">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MDMA</option>
                                    <option value="4aa4767c0f2d248775c3ad42">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MDA</option>
                                    <option value="826b835b961b0176d9bad05d">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Methylone &amp; BK</option>
                                    <option value="f054c02a4d903216f7aa4fc4">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Other</option>
                                    <option value="4ad9894f08f858eb65671e09">&nbsp;&nbsp;&nbsp;&nbsp;Opioids</option>
                                    <option value="a2a2f15df03e5b233b2d78f6">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pills</option>
                                    <option value="431d63ba3b4c5ce65875cd98">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Heroin</option>
                                    <option value="d7f08bf7654d438f1eadeb09">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Opium</option>
                                    <option value="97437fece70570883d100a22">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Oxycodone</option>
                                    <option value="5ab249370da1a50b97304d4e">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Buprenorphine</option>
                                    <option value="e91eff1279f906e64ef4e065">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Methadone</option>
                                    <option value="ac14e97e4d2cd849cfc9b145">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Codeine</option>
                                    <option value="558d2936132ac0a5bdd9ed89">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Other</option>
                                    <option value="de3b6f31e81e6ecdf871e5b2">&nbsp;&nbsp;&nbsp;&nbsp;Paraphernalia</option>
                                    <option value="493d7239f0d6c6f9c7f7f3b9">&nbsp;&nbsp;&nbsp;&nbsp;Prescription</option>
                                    <option value="a000b65b914f8badc1724ced">&nbsp;&nbsp;&nbsp;&nbsp;Psychedelics</option>
                                    <option value="f89d849b8e9dc2822a084060">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LSD</option>
                                    <option value="c8b88a6b4abf06bd42efd1ab">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Shrooms</option>
                                    <option value="9cc92eb94117f29fba4df45a">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DMT</option>
                                    <option value="8ab350292abc895dc66e6570">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mescaline</option>
                                    <option value="923fb99bc68a26a245b38553">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LSA</option>
                                    <option value="86bf6318ed034f648de11cd1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DMA / DOX</option>
                                    <option value="31489fa3222b339071937fb5">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NBOME</option>
                                    <option value="15624cb441f9cd1a3d1cbb5a">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2-CB</option>
                                    <option value="c8854e95f9b8f1aa7cfb3a89">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Other RCs</option>
                                    <option value="052b570446c246f40df1df4a">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Other</option>
                                    <option value="492bfbfc1992d66519cbb638">&nbsp;&nbsp;&nbsp;&nbsp;Steroids</option>
                                    <option value="d9a7a5b8138871a2a4a20616">&nbsp;&nbsp;&nbsp;&nbsp;Stimulants</option>
                                    <option value="529955108d4d287be92da9a9">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Cocaine</option>
                                    <option value="5fc17ccc16af8ade008eebf0">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Speed</option>
                                    <option value="48b500a7783f5b7e191bc666">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Meth</option>
                                    <option value="2f2dceeaae13424ec1537bc7">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Adderal &amp; Vyvanse</option>
                                    <option value="66eb26e231b7342a183c6931">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2-FA</option>
                                    <option value="bdffb198b969c3aaf06a39d5">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Other RCs</option>
                                    <option value="01001a24822cbcbd2ca70f28">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pressed Pills</option>
                                    <option value="403953600ef92e08a1f2d985">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Crack</option>
                                    <option value="3620a2b04ffd917e942c346c">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Other</option>
                                    <option value="e7489bfa085e0607e0c69f9f">&nbsp;&nbsp;&nbsp;&nbsp;Tobacco</option>
                                    <option value="06bcdf4e7fa5f65a3cbf7e14">&nbsp;&nbsp;&nbsp;&nbsp;Weight Loss</option>
                                    <option value="4de4fd067ce96e5ceca77514">&nbsp;&nbsp;&nbsp;&nbsp;Other</option>
                                    <option value="7d507b8f6527ad77dba96119">Counterfeit items</option>
                                    <option value="f6e5c1d08382a6a4ca347ae0">&nbsp;&nbsp;&nbsp;&nbsp;Clothing</option>
                                    <option value="e452cdb4af6100fe0c7ad8fc">&nbsp;&nbsp;&nbsp;&nbsp;Electronics</option>
                                    <option value="1e96de9b7de2c7c10652bd4d">&nbsp;&nbsp;&nbsp;&nbsp;Jewelry</option>
                                    <option value="a04e24e9c68dbf6600f38ddb">&nbsp;&nbsp;&nbsp;&nbsp;Money</option>
                                    <option value="254dd0d8d5af4f86b2487067">&nbsp;&nbsp;&nbsp;&nbsp;Fake IDs</option>
                                    <option value="572c4c633f39ecd7f623624a">&nbsp;&nbsp;&nbsp;&nbsp;Other</option>
                                    <option value="35f57be579e659eb66def24f">Digital Products</option>
                                    <option value="334ae5e9b366123c29e36370">&nbsp;&nbsp;&nbsp;&nbsp;E-Books</option>
                                    <option value="e81421ac8afc57a25defbdd7">&nbsp;&nbsp;&nbsp;&nbsp;Erotica</option>
                                    <option value="20cc21627781624d14dc0181">&nbsp;&nbsp;&nbsp;&nbsp;Fraud Software</option>
                                    <option value="b8a69e74de9b618dd8a8a948">&nbsp;&nbsp;&nbsp;&nbsp;Game Keys</option>
                                    <option value="20b4fc844099c8a41ef7d463">&nbsp;&nbsp;&nbsp;&nbsp;Legit Software</option>
                                    <option value="917f5e3d6eaa35728f97621e">&nbsp;&nbsp;&nbsp;&nbsp;Other</option>
                                    <option value="12a681b2c0500c570ab47e30">Fraud</option>
                                    <option value="f00bfcab07def6b3f322679a">&nbsp;&nbsp;&nbsp;&nbsp;Accounts &amp; Bank Drops</option>
                                    <option value="007b10f1428b99831f530838">&nbsp;&nbsp;&nbsp;&nbsp;CVV &amp; Cards</option>
                                    <option value="91afc662f086cd2c177d567f">&nbsp;&nbsp;&nbsp;&nbsp;Dumps</option>
                                    <option value="41c38829074a82f0a118ffd4">&nbsp;&nbsp;&nbsp;&nbsp;Personal Information &amp; Scans</option>
                                    <option value="78cbdf70d48a78c0a535bdab">&nbsp;&nbsp;&nbsp;&nbsp;Other</option>
                                    <option value="85e8f367512ef58b3a001fea">Guides &amp; Tutorials</option>
                                    <option value="63fb84e4b37636edd6380615">&nbsp;&nbsp;&nbsp;&nbsp;Drugs</option>
                                    <option value="f293df1d0dd45cc79c54365a">&nbsp;&nbsp;&nbsp;&nbsp;Fraud</option>
                                    <option value="a0773b3de70bdaca38acda2f">&nbsp;&nbsp;&nbsp;&nbsp;Hacking</option>
                                    <option value="a88c6e3918df83742217fecc">&nbsp;&nbsp;&nbsp;&nbsp;Security &amp; Anonymity</option>
                                    <option value="76063a1a1e610a88a2ae329f">&nbsp;&nbsp;&nbsp;&nbsp;Social Engineering</option>
                                    <option value="eeb65932716aa75e6ca3d621">&nbsp;&nbsp;&nbsp;&nbsp;Other</option>
                                    <option value="d8bb59271fafce8e97946166">Jewels &amp; Gold</option>
                                    <option value="01860d3c74ea9bbf9797b665">&nbsp;&nbsp;&nbsp;&nbsp;Gold</option>
                                    <option value="bc7c7a1e5b63fdd357728485">&nbsp;&nbsp;&nbsp;&nbsp;Silver</option>
                                    <option value="4acd143b9c0c572fc6e88d67">&nbsp;&nbsp;&nbsp;&nbsp;Other</option>
                                    <option value="9ad2fca9b3b81e82ca3c7dad">Carded items</option>
                                    <option value="bddd825101fcf992af3fa321">&nbsp;&nbsp;&nbsp;&nbsp;Appliances</option>
                                    <option value="cb7f3623bcfd6224c5bb8bad">&nbsp;&nbsp;&nbsp;&nbsp;Clothing</option>
                                    <option value="c498b253f64d5ae95366db9d">&nbsp;&nbsp;&nbsp;&nbsp;Digital</option>
                                    <option value="ef9ab40fe58f64436ee58fed">&nbsp;&nbsp;&nbsp;&nbsp;Electronics</option>
                                    <option value="9152d7878e8655f6526c1985">&nbsp;&nbsp;&nbsp;&nbsp;Other</option>
                                    <option value="4027cc32983f4e78a2873b3e">Services</option>
                                    <option value="1c29a89f7a4022133cab877d">&nbsp;&nbsp;&nbsp;&nbsp;Social Engineering</option>
                                    <option value="1b17857dc74c11953df85c55">&nbsp;&nbsp;&nbsp;&nbsp;Carding</option>
                                    <option value="09c02b4efe3af728f58d4e7d">&nbsp;&nbsp;&nbsp;&nbsp;Other</option>
                                    <option value="1a147b7d876b0943ca470427">Software &amp; Malware</option>
                                    <option value="475756f633d0cc71f0c868bd">&nbsp;&nbsp;&nbsp;&nbsp;Botnets &amp; Malware</option>
                                    <option value="449a1f423b9c4fc39ffa3956">&nbsp;&nbsp;&nbsp;&nbsp;Exploits</option>
                                    <option value="4e5630455a6f16bf43092060">&nbsp;&nbsp;&nbsp;&nbsp;Exploits Kits</option>
                                    <option value="c7da5f6d7fed7ebd27e4ea75">&nbsp;&nbsp;&nbsp;&nbsp;Security Software</option>
                                    <option value="3d17a531a2f89cab808604e0">&nbsp;&nbsp;&nbsp;&nbsp;Other</option>
                                    <option value="fca36877fbe3d7edf0029547">Security &amp; Hosting</option>
                                    <option value="79c2a100af215da6d3987ea6">&nbsp;&nbsp;&nbsp;&nbsp;Hosting</option>
                                    <option value="b2fde5841f17864159a3f1a4">&nbsp;&nbsp;&nbsp;&nbsp;VPN</option>
                                    <option value="74bf1a8740da2243d88eeddb">&nbsp;&nbsp;&nbsp;&nbsp;Socks</option>
                                    <option value="d8b489df08f3179c92e5521c">&nbsp;&nbsp;&nbsp;&nbsp;Shells</option>
                                    <option value="744db9866d0fbcb42d7c3da8">&nbsp;&nbsp;&nbsp;&nbsp;Cpanels</option>
                                    <option value="3d65cc292d35cb561b626d88">&nbsp;&nbsp;&nbsp;&nbsp;Other</option>
                                    <option value="1899e6354798484de1bfad50">Other Listings</option>
                                </select>

                                <!-- ====================== -->

                                <div class="w-full hidden peer-checked:block anim anim-fadeIn ">

                                    <input type="checkbox" name="cats" value="1" id="cats" class="absolute peer hidden">

                                    <label for="cats" id="cats-lb" class="text-xs rounded-md border-solid border-[1px] border-border text-abacus hover:bg-abacus hover:text-white my-1 text-left flex items-center pl-1"><i class="gg-chevron-right"></i>Search in multiple categories.</label>

                                    <div id="cats-cont" class="infoboxbody border-solid border-[1px] border-abacus2 hidden flex-col rounded-md !pt-2">

                                        <ul class="sidebar-categories">
                                            <li>

                                                <div class="sidebar-categories__item parent">



                                                    <input name="fcats[]" value="60792aab2b9afc46f066b529" type="checkbox" class="sidebar-categories__checkbox">
                                                    <a class="cat-name">
                                                        <strong class="mx-2">Drugs &amp; Chemicals</strong>
                                                    </a>


                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="a57acb011e82fe31ea5b8493" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Benzos </strong>
                                                            </a>

                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="607d0bdf688485a9ae59d7dd" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Pills</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="4ae60fd1377c6e87c12c6f0c" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Powder</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="44007a13d0f1dbc2bc007f3a" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">RC</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="252973d6359fad98502a5dd0" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Other</strong>
                                                                </a>
                                                            </div>


                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="e88ee39c23db7ebfb5d581be" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Cannabis &amp; Hashish </strong>
                                                            </a>

                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="3494d026c3bcf57e3ec62d6e" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Buds &amp; Flowers</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="8f984cf7a02ee25880aedcfa" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Shake</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="6c1816a1a5a42654eff928e3" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Concentrates</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="9b66a5126f06548921cf5a47" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Hash</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="4ae717b40185fcb776302b5c" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Prerolls</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="ebde2caabf602f5c5e4f7897" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Edibles</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="5676785600c4c27d03843f26" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Topicals &amp; Others</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="c8b853a11384e15ec7db92a8" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Seeds</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="5f9314754f2fdb550b203d84" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Synthetic</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="5f1c232f71afee2bddfe76e9" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Cart</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="6b42cb0301794710c4277455" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Other</strong>
                                                                </a>
                                                            </div>


                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="d9c3eb66627d1da608b19f30" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Dissociatives </strong>
                                                            </a>

                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="cf4e5423fb060eafede41c81" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Ketamine</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="d49237fb4830f373f178c53a" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">MXE</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="cf787dadcaf005bfb8601b79" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">GHB</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="827ff78d82d47eff17202b74" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Other</strong>
                                                                </a>
                                                            </div>


                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="748fb852dc76fb8c1ac1e525" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Ecstasy </strong>
                                                            </a>

                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="8a994f98835a08c7597e77ba" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Pills</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="5789a88768a95c9e847d7e52" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">MDMA</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="4aa4767c0f2d248775c3ad42" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">MDA</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="826b835b961b0176d9bad05d" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Methylone &amp; BK</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="f054c02a4d903216f7aa4fc4" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Other</strong>
                                                                </a>
                                                            </div>


                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="4ad9894f08f858eb65671e09" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Opioids </strong>
                                                            </a>

                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="a2a2f15df03e5b233b2d78f6" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Pills</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="431d63ba3b4c5ce65875cd98" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Heroin</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="d7f08bf7654d438f1eadeb09" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Opium</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="97437fece70570883d100a22" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Oxycodone</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="5ab249370da1a50b97304d4e" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Buprenorphine</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="e91eff1279f906e64ef4e065" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Methadone</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="ac14e97e4d2cd849cfc9b145" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Codeine</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="558d2936132ac0a5bdd9ed89" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Other</strong>
                                                                </a>
                                                            </div>


                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="de3b6f31e81e6ecdf871e5b2" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Paraphernalia </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="493d7239f0d6c6f9c7f7f3b9" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Prescription </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="a000b65b914f8badc1724ced" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Psychedelics </strong>
                                                            </a>

                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="f89d849b8e9dc2822a084060" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">LSD</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="c8b88a6b4abf06bd42efd1ab" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Shrooms</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="9cc92eb94117f29fba4df45a" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">DMT</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="8ab350292abc895dc66e6570" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Mescaline</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="923fb99bc68a26a245b38553" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">LSA</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="86bf6318ed034f648de11cd1" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">DMA / DOX</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="31489fa3222b339071937fb5" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">NBOME</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="15624cb441f9cd1a3d1cbb5a" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">2-CB</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="c8854e95f9b8f1aa7cfb3a89" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Other RCs</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="052b570446c246f40df1df4a" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Other</strong>
                                                                </a>
                                                            </div>


                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="492bfbfc1992d66519cbb638" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Steroids </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="d9a7a5b8138871a2a4a20616" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Stimulants </strong>
                                                            </a>

                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="529955108d4d287be92da9a9" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Cocaine</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="5fc17ccc16af8ade008eebf0" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Speed</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="48b500a7783f5b7e191bc666" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Meth</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="2f2dceeaae13424ec1537bc7" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Adderal &amp; Vyvanse</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="66eb26e231b7342a183c6931" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">2-FA</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="bdffb198b969c3aaf06a39d5" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Other RCs</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="01001a24822cbcbd2ca70f28" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Pressed Pills</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="403953600ef92e08a1f2d985" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Crack</strong>
                                                                </a>
                                                            </div>
                                                            <div class="sub-cat">
                                                                <input name="fcats[]" value="3620a2b04ffd917e942c346c" type="checkbox" class="sidebar-categories__checkbox2">
                                                                <a class="cat-name">
                                                                    <strong class="mx-2">Other</strong>
                                                                </a>
                                                            </div>


                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="e7489bfa085e0607e0c69f9f" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Tobacco </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="06bcdf4e7fa5f65a3cbf7e14" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Weight Loss </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="4de4fd067ce96e5ceca77514" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Other </strong>
                                                            </a>



                                                        </div>
                                                    </div>



                                                </div>

                                                <hr>

                                                <div class="sidebar-categories__item parent">



                                                    <input name="fcats[]" value="7d507b8f6527ad77dba96119" type="checkbox" class="sidebar-categories__checkbox">
                                                    <a class="cat-name">
                                                        <strong class="mx-2">Counterfeit items</strong>
                                                    </a>


                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="f6e5c1d08382a6a4ca347ae0" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Clothing </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="e452cdb4af6100fe0c7ad8fc" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Electronics </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="1e96de9b7de2c7c10652bd4d" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Jewelry </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="a04e24e9c68dbf6600f38ddb" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Money </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="254dd0d8d5af4f86b2487067" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Fake IDs </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="572c4c633f39ecd7f623624a" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Other </strong>
                                                            </a>



                                                        </div>
                                                    </div>



                                                </div>

                                                <hr>

                                                <div class="sidebar-categories__item parent">



                                                    <input name="fcats[]" value="35f57be579e659eb66def24f" type="checkbox" class="sidebar-categories__checkbox">
                                                    <a class="cat-name">
                                                        <strong class="mx-2">Digital Products</strong>
                                                    </a>


                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="334ae5e9b366123c29e36370" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">E-Books </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="e81421ac8afc57a25defbdd7" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Erotica </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="20cc21627781624d14dc0181" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Fraud Software </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="b8a69e74de9b618dd8a8a948" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Game Keys </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="20b4fc844099c8a41ef7d463" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Legit Software </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="917f5e3d6eaa35728f97621e" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Other </strong>
                                                            </a>



                                                        </div>
                                                    </div>



                                                </div>

                                                <hr>

                                                <div class="sidebar-categories__item parent">



                                                    <input name="fcats[]" value="12a681b2c0500c570ab47e30" type="checkbox" class="sidebar-categories__checkbox">
                                                    <a class="cat-name">
                                                        <strong class="mx-2">Fraud</strong>
                                                    </a>


                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="f00bfcab07def6b3f322679a" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Accounts &amp; Bank Drops </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="007b10f1428b99831f530838" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">CVV &amp; Cards </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="91afc662f086cd2c177d567f" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Dumps </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="41c38829074a82f0a118ffd4" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Personal Information &amp; Scans </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="78cbdf70d48a78c0a535bdab" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Other </strong>
                                                            </a>



                                                        </div>
                                                    </div>



                                                </div>

                                                <hr>

                                                <div class="sidebar-categories__item parent">



                                                    <input name="fcats[]" value="85e8f367512ef58b3a001fea" type="checkbox" class="sidebar-categories__checkbox">
                                                    <a class="cat-name">
                                                        <strong class="mx-2">Guides &amp; Tutorials</strong>
                                                    </a>


                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="63fb84e4b37636edd6380615" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Drugs </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="f293df1d0dd45cc79c54365a" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Fraud </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="a0773b3de70bdaca38acda2f" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Hacking </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="a88c6e3918df83742217fecc" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Security &amp; Anonymity </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="76063a1a1e610a88a2ae329f" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Social Engineering </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="eeb65932716aa75e6ca3d621" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Other </strong>
                                                            </a>



                                                        </div>
                                                    </div>



                                                </div>

                                                <hr>

                                                <div class="sidebar-categories__item parent">



                                                    <input name="fcats[]" value="d8bb59271fafce8e97946166" type="checkbox" class="sidebar-categories__checkbox">
                                                    <a class="cat-name">
                                                        <strong class="mx-2">Jewels &amp; Gold</strong>
                                                    </a>


                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="01860d3c74ea9bbf9797b665" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Gold </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="bc7c7a1e5b63fdd357728485" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Silver </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="4acd143b9c0c572fc6e88d67" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Other </strong>
                                                            </a>



                                                        </div>
                                                    </div>



                                                </div>

                                                <hr>

                                                <div class="sidebar-categories__item parent">



                                                    <input name="fcats[]" value="9ad2fca9b3b81e82ca3c7dad" type="checkbox" class="sidebar-categories__checkbox">
                                                    <a class="cat-name">
                                                        <strong class="mx-2">Carded items</strong>
                                                    </a>


                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="bddd825101fcf992af3fa321" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Appliances </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="cb7f3623bcfd6224c5bb8bad" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Clothing </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="c498b253f64d5ae95366db9d" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Digital </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="ef9ab40fe58f64436ee58fed" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Electronics </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="9152d7878e8655f6526c1985" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Other </strong>
                                                            </a>



                                                        </div>
                                                    </div>



                                                </div>

                                                <hr>

                                                <div class="sidebar-categories__item parent">



                                                    <input name="fcats[]" value="4027cc32983f4e78a2873b3e" type="checkbox" class="sidebar-categories__checkbox">
                                                    <a class="cat-name">
                                                        <strong class="mx-2">Services</strong>
                                                    </a>


                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="1c29a89f7a4022133cab877d" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Social Engineering </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="1b17857dc74c11953df85c55" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Carding </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="09c02b4efe3af728f58d4e7d" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Other </strong>
                                                            </a>



                                                        </div>
                                                    </div>



                                                </div>

                                                <hr>

                                                <div class="sidebar-categories__item parent">



                                                    <input name="fcats[]" value="1a147b7d876b0943ca470427" type="checkbox" class="sidebar-categories__checkbox">
                                                    <a class="cat-name">
                                                        <strong class="mx-2">Software &amp; Malware</strong>
                                                    </a>


                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="475756f633d0cc71f0c868bd" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Botnets &amp; Malware </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="449a1f423b9c4fc39ffa3956" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Exploits </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="4e5630455a6f16bf43092060" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Exploits Kits </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="c7da5f6d7fed7ebd27e4ea75" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Security Software </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="3d17a531a2f89cab808604e0" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Other </strong>
                                                            </a>



                                                        </div>
                                                    </div>



                                                </div>

                                                <hr>

                                                <div class="sidebar-categories__item parent">



                                                    <input name="fcats[]" value="fca36877fbe3d7edf0029547" type="checkbox" class="sidebar-categories__checkbox">
                                                    <a class="cat-name">
                                                        <strong class="mx-2">Security &amp; Hosting</strong>
                                                    </a>


                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="79c2a100af215da6d3987ea6" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Hosting </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="b2fde5841f17864159a3f1a4" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">VPN </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="74bf1a8740da2243d88eeddb" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Socks </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="d8b489df08f3179c92e5521c" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Shells </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="744db9866d0fbcb42d7c3da8" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Cpanels </strong>
                                                            </a>



                                                        </div>
                                                    </div>

                                                    <div class="sub-cat">
                                                        <div class="sidebar-categories__item parent">


                                                            <input name="fcats[]" value="3d65cc292d35cb561b626d88" type="checkbox" class="sidebar-categories__checkbox">
                                                            <a class="cat-name">
                                                                <strong class="mx-2">Other </strong>
                                                            </a>



                                                        </div>
                                                    </div>



                                                </div>

                                                <hr>

                                                <div class="sidebar-categories__item parent">
                                                    <input name="fcats[]" value="1899e6354798484de1bfad50" type="checkbox" class="sidebar-categories__checkbox">
                                                    <a class="cat-name">
                                                        <strong class="mx-2">Other Listings</strong>
                                                    </a>
                                                </div>

                                                <hr>


                                            </li>
                                        </ul>

                                    </div>
                                </div>

                                <!-- ====================== -->

                                <h3 class="hidden peer-checked:flex anim anim-fadeIn std">Product type:</h3>
                                <div class="hidden peer-checked:flex anim anim-fadeIn justify-evenly items-center">
                                    <input name="s_lphysical" value="2" checked="checked" type="radio">

                                    <span class="std">All</span> &nbsp;
                                    <input name="s_lphysical" value="0" type="radio">

                                    <span class="std">Digital</span> &nbsp;

                                    <input name="s_lphysical" value="1" type="radio">

                                    <span class="std">Physical
        </span></div>
                                <h3 class="hidden peer-checked:flex anim anim-fadeIn std">Price range:</h3>

                                <span class="hidden peer-checked:flex anim anim-fadeIn std w-full gap-2 justify-evenly ">

            <div class="flex items-center  w-1/2">
                From&nbsp; <input name="s_minprice" size="6" class="std w-4/10 text-right" value="0.00" type="text">&nbsp;USD
            </div>

            <div class="flex items-center  w-1/2">
                to&nbsp;<input name="s_maxprice" size="9" class="std w-1/2" value="99999.99" type="text">&nbsp;USD
            </div>

        </span>


                                <h3 class="hidden peer-checked:flex anim anim-fadeIn std">Cryptocurrencies:</h3>
                                <select name="s_crypto" class="hidden peer-checked:flex anim anim-fadeIn std">

                                    <option value="0" selected="selected">All listings</option>

                                    <option value="1">Show only bitcoin listings</option>

                                    <option value="2">Show only monero listings</option>
                                </select><h3 class="hidden peer-checked:flex anim anim-fadeIn std">Automatic fulfillment:</h3>
                                <select name="s_fulfill" class="hidden peer-checked:flex anim anim-fadeIn std">

                                    <option value="2" selected="selected">All listings</option>

                                    <option value="1">Show only automatic listings</option>

                                    <option value="0">Show only manual listings</option>
                                </select>
                                <h3 class="hidden peer-checked:flex anim anim-fadeIn std">Multisig options:</h3>
                                <select name="s_multisig" class="hidden peer-checked:flex anim anim-fadeIn std">

                                    <option value="2" selected="selected">All listings</option>

                                    <option value="1">Show only multisig listings</option>

                                    <option value="0">Show only regular Escrow listings</option>
                                </select><h3 class="hidden peer-checked:flex anim anim-fadeIn std">Bulk discounts:</h3>
                                <select name="s_bulk" class="hidden peer-checked:flex anim anim-fadeIn std">

                                    <option value="2" selected="selected">All listings</option>

                                    <option value="1">Only listings with bulk discounts</option>

                                    <option value="0">Only flat rate listings</option>
                                </select><h3 class="hidden peer-checked:flex anim anim-fadeIn std">In stock:</h3>
                                <select name="s_stock" class="hidden peer-checked:flex anim anim-fadeIn std">

                                    <option value="2">All listings</option>

                                    <option value="1" selected="selected">Only items in stock</option>

                                    <option value="0">Only items out of stock</option>
                                </select><h3 class="hidden peer-checked:flex anim anim-fadeIn std">Payment type:</h3>
                                <select name="s_payment" class="hidden peer-checked:flex anim anim-fadeIn std">

                                    <option value="0" selected="selected">All listings</option>

                                    <option value="1">Escrow</option>

                                    <option value="2">Partial FE percentage</option>

                                    <option value="3">Total FE</option>
                                </select>

                                <div class="mx-auto flex gap-2 items-center" style="margin-top: 10px;">
                                    <input id="search-btn" class="bg-abacus2 hover:bg-abacus px-2 py-0.5 block text-white text-sm font-bold w-max rounded border-none" value="Search" type="submit">
                                </div>


                                <!-- show/hide advanced filters -->

                                <label for="adv" class="text-xs font-bold uppercase py-1 px-2 rounded-md w-max mx-auto bg-abacus text-white hover:bg-abacus2 peer-checked:hidden flex items-center justify-center mt-4 mb-1">
                                    <i class="gg-math-plus mr-1"></i>Show advanced filters
                                </label>

                                <label for="adv" class="text-xs font-bold uppercase py-1 px-2 rounded-md w-max mx-auto bg-abacus2 hover:bg-abacus text-white hidden peer-checked:flex items-center justify-center mt-4 mb-1">
                                    <b class="bg-white rounded mr-2" style="height: 2px; width: 10px;"></b>Hide advanced filters
                                </label>

                            </div>

                        </div>

                    </form>












                    <form name="formHomesidebar" action="/search" method="get" class="w-full flex flex-col gap-2 ">

                        <!-- browser categories -->

                        <input type="checkbox" name="cats2" id="cats2" class="absolute peer hidden">

                        <div id="cat2" class="bg-white border-solid border-[1px] border-border rounded-md p-2 hover:!border-abacus2 mx-auto my-0 !w-full h-[fit-content]">

                            <!-- starting cats !-->


                            <label for="cats2" class="cursor-pointer 2xl:cursor-default">
                                <h1 class="text-[13px] font-bold pl-[10px] m-0 uppercase flex items-center justify-start 2xl:justify-center !text-abacus !bg-white 2xl:!bg-abacus 2xl:!text-white !rounded-md !border-none py-2"><i class="gg-eye mr-3"></i> Browse Categories</h1>

                            </label>

                            <!-- checkboxes for categories -->

                            <div id="cats2-cont" class="px-0.5 py-1 hidden 2xl:flex flex-col">
                                <div class="text-xs text-gray-500 my-1 italic text-center" style="margin-top: 2px; margin-bottom: 8px;">Click on the left arrow to see subcategories.</div>
                                <ul class="sidebar-categories">

                                    <li>
                                        <div class="sidebar-categories__item parent">



                                            <input name="fcats[]" value="60792aab2b9afc46f066b529" type="checkbox" class="sidebar-categories__checkbox">
                                            <a href="/search?fcats[]=60792aab2b9afc46f066b529&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                <strong class="mx-2">Drugs &amp; Chemicals</strong><b style="margin-left: 67%; position: absolute;"> (28250)</b>
                                            </a>


                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=a57acb011e82fe31ea5b8493&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Benzos </strong><b style="margin-left: 67%; position: absolute;"> (2241)</b>
                                                    </a>

                                                    <input name="fcats[]" value="a57acb011e82fe31ea5b8493" type="checkbox" class="sidebar-categories__checkbox">
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="607d0bdf688485a9ae59d7dd" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=607d0bdf688485a9ae59d7dd&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Pills</strong><b style="margin-left: 55%; position: absolute;"> (2013)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="4ae60fd1377c6e87c12c6f0c" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=4ae60fd1377c6e87c12c6f0c&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Powder</strong><b style="margin-left: 55%; position: absolute;"> (77)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="44007a13d0f1dbc2bc007f3a" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=44007a13d0f1dbc2bc007f3a&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">RC</strong><b style="margin-left: 55%; position: absolute;"> (63)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="252973d6359fad98502a5dd0" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=252973d6359fad98502a5dd0&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Other</strong><b style="margin-left: 55%; position: absolute;"> (88)</b>
                                                        </a>
                                                    </div>


                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=e88ee39c23db7ebfb5d581be&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Cannabis &amp; Hashish </strong><b style="margin-left: 67%; position: absolute;"> (6724)</b>
                                                    </a>

                                                    <input name="fcats[]" value="e88ee39c23db7ebfb5d581be" type="checkbox" class="sidebar-categories__checkbox">
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="3494d026c3bcf57e3ec62d6e" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=3494d026c3bcf57e3ec62d6e&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Buds &amp; Flowers</strong><b style="margin-left: 55%; position: absolute;"> (3726)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="8f984cf7a02ee25880aedcfa" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=8f984cf7a02ee25880aedcfa&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Shake</strong><b style="margin-left: 55%; position: absolute;"> (187)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="6c1816a1a5a42654eff928e3" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=6c1816a1a5a42654eff928e3&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Concentrates</strong><b style="margin-left: 55%; position: absolute;"> (674)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="9b66a5126f06548921cf5a47" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=9b66a5126f06548921cf5a47&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Hash</strong><b style="margin-left: 55%; position: absolute;"> (924)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="4ae717b40185fcb776302b5c" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=4ae717b40185fcb776302b5c&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Prerolls</strong><b style="margin-left: 55%; position: absolute;"> (58)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="ebde2caabf602f5c5e4f7897" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=ebde2caabf602f5c5e4f7897&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Edibles</strong><b style="margin-left: 55%; position: absolute;"> (435)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="5676785600c4c27d03843f26" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=5676785600c4c27d03843f26&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Topicals &amp; Others</strong><b style="margin-left: 55%; position: absolute;"> (35)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="c8b853a11384e15ec7db92a8" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=c8b853a11384e15ec7db92a8&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Seeds</strong><b style="margin-left: 55%; position: absolute;"> (84)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="5f9314754f2fdb550b203d84" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=5f9314754f2fdb550b203d84&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Synthetic</strong><b style="margin-left: 55%; position: absolute;"> (83)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="5f1c232f71afee2bddfe76e9" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=5f1c232f71afee2bddfe76e9&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Cart</strong><b style="margin-left: 55%; position: absolute;"> (478)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="6b42cb0301794710c4277455" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=6b42cb0301794710c4277455&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Other</strong><b style="margin-left: 55%; position: absolute;"> (40)</b>
                                                        </a>
                                                    </div>


                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=d9c3eb66627d1da608b19f30&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Dissociatives </strong><b style="margin-left: 67%; position: absolute;"> (2013)</b>
                                                    </a>

                                                    <input name="fcats[]" value="d9c3eb66627d1da608b19f30" type="checkbox" class="sidebar-categories__checkbox">
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="cf4e5423fb060eafede41c81" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=cf4e5423fb060eafede41c81&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Ketamine</strong><b style="margin-left: 55%; position: absolute;"> (1790)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="d49237fb4830f373f178c53a" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=d49237fb4830f373f178c53a&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">MXE</strong><b style="margin-left: 55%; position: absolute;"> (0)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="cf787dadcaf005bfb8601b79" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=cf787dadcaf005bfb8601b79&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">GHB</strong><b style="margin-left: 55%; position: absolute;"> (171)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="827ff78d82d47eff17202b74" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=827ff78d82d47eff17202b74&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Other</strong><b style="margin-left: 55%; position: absolute;"> (52)</b>
                                                        </a>
                                                    </div>


                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=748fb852dc76fb8c1ac1e525&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Ecstasy </strong><b style="margin-left: 67%; position: absolute;"> (2451)</b>
                                                    </a>

                                                    <input name="fcats[]" value="748fb852dc76fb8c1ac1e525" type="checkbox" class="sidebar-categories__checkbox">
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="8a994f98835a08c7597e77ba" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=8a994f98835a08c7597e77ba&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Pills</strong><b style="margin-left: 55%; position: absolute;"> (1099)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="5789a88768a95c9e847d7e52" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=5789a88768a95c9e847d7e52&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">MDMA</strong><b style="margin-left: 55%; position: absolute;"> (1290)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="4aa4767c0f2d248775c3ad42" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=4aa4767c0f2d248775c3ad42&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">MDA</strong><b style="margin-left: 55%; position: absolute;"> (9)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="826b835b961b0176d9bad05d" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=826b835b961b0176d9bad05d&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Methylone &amp; BK</strong><b style="margin-left: 55%; position: absolute;"> (9)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="f054c02a4d903216f7aa4fc4" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=f054c02a4d903216f7aa4fc4&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Other</strong><b style="margin-left: 55%; position: absolute;"> (44)</b>
                                                        </a>
                                                    </div>


                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=4ad9894f08f858eb65671e09&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Opioids </strong><b style="margin-left: 67%; position: absolute;"> (2382)</b>
                                                    </a>

                                                    <input name="fcats[]" value="4ad9894f08f858eb65671e09" type="checkbox" class="sidebar-categories__checkbox">
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="a2a2f15df03e5b233b2d78f6" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=a2a2f15df03e5b233b2d78f6&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Pills</strong><b style="margin-left: 55%; position: absolute;"> (645)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="431d63ba3b4c5ce65875cd98" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=431d63ba3b4c5ce65875cd98&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Heroin</strong><b style="margin-left: 55%; position: absolute;"> (684)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="d7f08bf7654d438f1eadeb09" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=d7f08bf7654d438f1eadeb09&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Opium</strong><b style="margin-left: 55%; position: absolute;"> (41)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="97437fece70570883d100a22" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=97437fece70570883d100a22&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Oxycodone</strong><b style="margin-left: 55%; position: absolute;"> (633)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="5ab249370da1a50b97304d4e" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=5ab249370da1a50b97304d4e&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Buprenorphine</strong><b style="margin-left: 55%; position: absolute;"> (40)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="e91eff1279f906e64ef4e065" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=e91eff1279f906e64ef4e065&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Methadone</strong><b style="margin-left: 55%; position: absolute;"> (16)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="ac14e97e4d2cd849cfc9b145" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=ac14e97e4d2cd849cfc9b145&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Codeine</strong><b style="margin-left: 55%; position: absolute;"> (134)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="558d2936132ac0a5bdd9ed89" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=558d2936132ac0a5bdd9ed89&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Other</strong><b style="margin-left: 55%; position: absolute;"> (189)</b>
                                                        </a>
                                                    </div>


                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=de3b6f31e81e6ecdf871e5b2&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Paraphernalia </strong><b style="margin-left: 67%; position: absolute;"> (1)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=493d7239f0d6c6f9c7f7f3b9&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Prescription </strong><b style="margin-left: 67%; position: absolute;"> (2208)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=a000b65b914f8badc1724ced&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Psychedelics </strong><b style="margin-left: 67%; position: absolute;"> (2685)</b>
                                                    </a>

                                                    <input name="fcats[]" value="a000b65b914f8badc1724ced" type="checkbox" class="sidebar-categories__checkbox">
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="f89d849b8e9dc2822a084060" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=f89d849b8e9dc2822a084060&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">LSD</strong><b style="margin-left: 55%; position: absolute;"> (1438)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="c8b88a6b4abf06bd42efd1ab" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=c8b88a6b4abf06bd42efd1ab&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Shrooms</strong><b style="margin-left: 55%; position: absolute;"> (462)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="9cc92eb94117f29fba4df45a" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=9cc92eb94117f29fba4df45a&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">DMT</strong><b style="margin-left: 55%; position: absolute;"> (237)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="8ab350292abc895dc66e6570" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=8ab350292abc895dc66e6570&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Mescaline</strong><b style="margin-left: 55%; position: absolute;"> (27)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="923fb99bc68a26a245b38553" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=923fb99bc68a26a245b38553&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">LSA</strong><b style="margin-left: 55%; position: absolute;"> (1)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="86bf6318ed034f648de11cd1" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=86bf6318ed034f648de11cd1&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">DMA / DOX</strong><b style="margin-left: 55%; position: absolute;"> (3)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="31489fa3222b339071937fb5" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=31489fa3222b339071937fb5&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">NBOME</strong><b style="margin-left: 55%; position: absolute;"> (13)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="15624cb441f9cd1a3d1cbb5a" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=15624cb441f9cd1a3d1cbb5a&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">2-CB</strong><b style="margin-left: 55%; position: absolute;"> (386)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="c8854e95f9b8f1aa7cfb3a89" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=c8854e95f9b8f1aa7cfb3a89&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Other RCs</strong><b style="margin-left: 55%; position: absolute;"> (50)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="052b570446c246f40df1df4a" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=052b570446c246f40df1df4a&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Other</strong><b style="margin-left: 55%; position: absolute;"> (68)</b>
                                                        </a>
                                                    </div>


                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=492bfbfc1992d66519cbb638&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Steroids </strong><b style="margin-left: 67%; position: absolute;"> (1205)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=d9a7a5b8138871a2a4a20616&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Stimulants </strong><b style="margin-left: 67%; position: absolute;"> (6080)</b>
                                                    </a>

                                                    <input name="fcats[]" value="d9a7a5b8138871a2a4a20616" type="checkbox" class="sidebar-categories__checkbox">
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="529955108d4d287be92da9a9" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=529955108d4d287be92da9a9&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Cocaine</strong><b style="margin-left: 55%; position: absolute;"> (2085)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="5fc17ccc16af8ade008eebf0" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=5fc17ccc16af8ade008eebf0&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Speed</strong><b style="margin-left: 55%; position: absolute;"> (1049)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="48b500a7783f5b7e191bc666" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=48b500a7783f5b7e191bc666&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Meth</strong><b style="margin-left: 55%; position: absolute;"> (1243)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="2f2dceeaae13424ec1537bc7" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=2f2dceeaae13424ec1537bc7&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Adderal &amp; Vyvanse</strong><b style="margin-left: 55%; position: absolute;"> (709)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="66eb26e231b7342a183c6931" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=66eb26e231b7342a183c6931&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">2-FA</strong><b style="margin-left: 55%; position: absolute;"> (0)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="bdffb198b969c3aaf06a39d5" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=bdffb198b969c3aaf06a39d5&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Other RCs</strong><b style="margin-left: 55%; position: absolute;"> (263)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="01001a24822cbcbd2ca70f28" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=01001a24822cbcbd2ca70f28&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Pressed Pills</strong><b style="margin-left: 55%; position: absolute;"> (50)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="403953600ef92e08a1f2d985" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=403953600ef92e08a1f2d985&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Crack</strong><b style="margin-left: 55%; position: absolute;"> (103)</b>
                                                        </a>
                                                    </div>
                                                    <div class="sub-cat">
                                                        <input name="fcats[]" value="3620a2b04ffd917e942c346c" type="checkbox" class="sidebar-categories__checkbox2">
                                                        <a href="/search?fcats[]=3620a2b04ffd917e942c346c&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                            <strong class="mx-2">Other</strong><b style="margin-left: 55%; position: absolute;"> (578)</b>
                                                        </a>
                                                    </div>


                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=e7489bfa085e0607e0c69f9f&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Tobacco </strong><b style="margin-left: 67%; position: absolute;"> (11)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=06bcdf4e7fa5f65a3cbf7e14&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Weight Loss </strong><b style="margin-left: 67%; position: absolute;"> (123)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=4de4fd067ce96e5ceca77514&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Other </strong><b style="margin-left: 67%; position: absolute;"> (126)</b>
                                                    </a>



                                                </div>
                                            </div>




                                        </div>

                                        <hr>

                                        <div class="sidebar-categories__item parent">



                                            <input name="fcats[]" value="7d507b8f6527ad77dba96119" type="checkbox" class="sidebar-categories__checkbox">
                                            <a href="/search?fcats[]=7d507b8f6527ad77dba96119&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                <strong class="mx-2">Counterfeit items</strong><b style="margin-left: 67%; position: absolute;"> (231)</b>
                                            </a>


                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=f6e5c1d08382a6a4ca347ae0&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Clothing </strong><b style="margin-left: 67%; position: absolute;"> (52)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=e452cdb4af6100fe0c7ad8fc&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Electronics </strong><b style="margin-left: 67%; position: absolute;"> (9)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=1e96de9b7de2c7c10652bd4d&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Jewelry </strong><b style="margin-left: 67%; position: absolute;"> (0)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=a04e24e9c68dbf6600f38ddb&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Money </strong><b style="margin-left: 67%; position: absolute;"> (28)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=254dd0d8d5af4f86b2487067&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Fake IDs </strong><b style="margin-left: 67%; position: absolute;"> (132)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=572c4c633f39ecd7f623624a&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Other </strong><b style="margin-left: 67%; position: absolute;"> (10)</b>
                                                    </a>



                                                </div>
                                            </div>




                                        </div>

                                        <hr>

                                        <div class="sidebar-categories__item parent">



                                            <input name="fcats[]" value="35f57be579e659eb66def24f" type="checkbox" class="sidebar-categories__checkbox">
                                            <a href="/search?fcats[]=35f57be579e659eb66def24f&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                <strong class="mx-2">Digital Products</strong><b style="margin-left: 67%; position: absolute;"> (4125)</b>
                                            </a>


                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=334ae5e9b366123c29e36370&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">E-Books </strong><b style="margin-left: 67%; position: absolute;"> (661)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=e81421ac8afc57a25defbdd7&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Erotica </strong><b style="margin-left: 67%; position: absolute;"> (1204)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=20cc21627781624d14dc0181&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Fraud Software </strong><b style="margin-left: 67%; position: absolute;"> (558)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=b8a69e74de9b618dd8a8a948&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Game Keys </strong><b style="margin-left: 67%; position: absolute;"> (22)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=20b4fc844099c8a41ef7d463&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Legit Software </strong><b style="margin-left: 67%; position: absolute;"> (606)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=917f5e3d6eaa35728f97621e&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Other </strong><b style="margin-left: 67%; position: absolute;"> (1074)</b>
                                                    </a>



                                                </div>
                                            </div>




                                        </div>

                                        <hr>

                                        <div class="sidebar-categories__item parent">



                                            <input name="fcats[]" value="12a681b2c0500c570ab47e30" type="checkbox" class="sidebar-categories__checkbox">
                                            <a href="/search?fcats[]=12a681b2c0500c570ab47e30&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                <strong class="mx-2">Fraud</strong><b style="margin-left: 67%; position: absolute;"> (5327)</b>
                                            </a>


                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=f00bfcab07def6b3f322679a&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Accounts &amp; Bank Drops </strong><b style="margin-left: 67%; position: absolute;"> (1912)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=007b10f1428b99831f530838&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">CVV &amp; Cards </strong><b style="margin-left: 67%; position: absolute;"> (571)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=91afc662f086cd2c177d567f&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Dumps </strong><b style="margin-left: 67%; position: absolute;"> (806)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=41c38829074a82f0a118ffd4&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Personal Information &amp; Scans </strong><b style="margin-left: 67%; position: absolute;"> (1305)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=78cbdf70d48a78c0a535bdab&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Other </strong><b style="margin-left: 67%; position: absolute;"> (733)</b>
                                                    </a>



                                                </div>
                                            </div>




                                        </div>

                                        <hr>

                                        <div class="sidebar-categories__item parent">



                                            <input name="fcats[]" value="85e8f367512ef58b3a001fea" type="checkbox" class="sidebar-categories__checkbox">
                                            <a href="/search?fcats[]=85e8f367512ef58b3a001fea&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                <strong class="mx-2">Guides &amp; Tutorials</strong><b style="margin-left: 67%; position: absolute;"> (4827)</b>
                                            </a>


                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=63fb84e4b37636edd6380615&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Drugs </strong><b style="margin-left: 67%; position: absolute;"> (227)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=f293df1d0dd45cc79c54365a&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Fraud </strong><b style="margin-left: 67%; position: absolute;"> (2163)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=a0773b3de70bdaca38acda2f&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Hacking </strong><b style="margin-left: 67%; position: absolute;"> (817)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=a88c6e3918df83742217fecc&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Security &amp; Anonymity </strong><b style="margin-left: 67%; position: absolute;"> (325)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=76063a1a1e610a88a2ae329f&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Social Engineering </strong><b style="margin-left: 67%; position: absolute;"> (437)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=eeb65932716aa75e6ca3d621&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Other </strong><b style="margin-left: 67%; position: absolute;"> (858)</b>
                                                    </a>



                                                </div>
                                            </div>




                                        </div>

                                        <hr>

                                        <div class="sidebar-categories__item parent">



                                            <input name="fcats[]" value="d8bb59271fafce8e97946166" type="checkbox" class="sidebar-categories__checkbox">
                                            <a href="/search?fcats[]=d8bb59271fafce8e97946166&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                <strong class="mx-2">Jewels &amp; Gold</strong><b style="margin-left: 67%; position: absolute;"> (36)</b>
                                            </a>


                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=01860d3c74ea9bbf9797b665&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Gold </strong><b style="margin-left: 67%; position: absolute;"> (36)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=bc7c7a1e5b63fdd357728485&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Silver </strong><b style="margin-left: 67%; position: absolute;"> (0)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=4acd143b9c0c572fc6e88d67&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Other </strong><b style="margin-left: 67%; position: absolute;"> (0)</b>
                                                    </a>



                                                </div>
                                            </div>




                                        </div>

                                        <hr>

                                        <div class="sidebar-categories__item parent">



                                            <input name="fcats[]" value="9ad2fca9b3b81e82ca3c7dad" type="checkbox" class="sidebar-categories__checkbox">
                                            <a href="/search?fcats[]=9ad2fca9b3b81e82ca3c7dad&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                <strong class="mx-2">Carded items</strong><b style="margin-left: 67%; position: absolute;"> (5)</b>
                                            </a>


                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=bddd825101fcf992af3fa321&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Appliances </strong><b style="margin-left: 67%; position: absolute;"> (0)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=cb7f3623bcfd6224c5bb8bad&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Clothing </strong><b style="margin-left: 67%; position: absolute;"> (0)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=c498b253f64d5ae95366db9d&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Digital </strong><b style="margin-left: 67%; position: absolute;"> (5)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=ef9ab40fe58f64436ee58fed&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Electronics </strong><b style="margin-left: 67%; position: absolute;"> (0)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=9152d7878e8655f6526c1985&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Other </strong><b style="margin-left: 67%; position: absolute;"> (0)</b>
                                                    </a>



                                                </div>
                                            </div>




                                        </div>

                                        <hr>

                                        <div class="sidebar-categories__item parent">



                                            <input name="fcats[]" value="4027cc32983f4e78a2873b3e" type="checkbox" class="sidebar-categories__checkbox">
                                            <a href="/search?fcats[]=4027cc32983f4e78a2873b3e&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                <strong class="mx-2">Services</strong><b style="margin-left: 67%; position: absolute;"> (525)</b>
                                            </a>


                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=1c29a89f7a4022133cab877d&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Social Engineering </strong><b style="margin-left: 67%; position: absolute;"> (217)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=1b17857dc74c11953df85c55&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Carding </strong><b style="margin-left: 67%; position: absolute;"> (170)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=09c02b4efe3af728f58d4e7d&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Other </strong><b style="margin-left: 67%; position: absolute;"> (138)</b>
                                                    </a>



                                                </div>
                                            </div>




                                        </div>

                                        <hr>

                                        <div class="sidebar-categories__item parent">



                                            <input name="fcats[]" value="1a147b7d876b0943ca470427" type="checkbox" class="sidebar-categories__checkbox">
                                            <a href="/search?fcats[]=1a147b7d876b0943ca470427&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                <strong class="mx-2">Software &amp; Malware</strong><b style="margin-left: 67%; position: absolute;"> (1271)</b>
                                            </a>


                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=475756f633d0cc71f0c868bd&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Botnets &amp; Malware </strong><b style="margin-left: 67%; position: absolute;"> (152)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=449a1f423b9c4fc39ffa3956&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Exploits </strong><b style="margin-left: 67%; position: absolute;"> (42)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=4e5630455a6f16bf43092060&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Exploits Kits </strong><b style="margin-left: 67%; position: absolute;"> (64)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=c7da5f6d7fed7ebd27e4ea75&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Security Software </strong><b style="margin-left: 67%; position: absolute;"> (285)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=3d17a531a2f89cab808604e0&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Other </strong><b style="margin-left: 67%; position: absolute;"> (728)</b>
                                                    </a>



                                                </div>
                                            </div>




                                        </div>

                                        <hr>

                                        <div class="sidebar-categories__item parent">



                                            <input name="fcats[]" value="fca36877fbe3d7edf0029547" type="checkbox" class="sidebar-categories__checkbox">
                                            <a href="/search?fcats[]=fca36877fbe3d7edf0029547&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                <strong class="mx-2">Security &amp; Hosting</strong><b style="margin-left: 67%; position: absolute;"> (171)</b>
                                            </a>


                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=79c2a100af215da6d3987ea6&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Hosting </strong><b style="margin-left: 67%; position: absolute;"> (12)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=b2fde5841f17864159a3f1a4&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">VPN </strong><b style="margin-left: 67%; position: absolute;"> (74)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=74bf1a8740da2243d88eeddb&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Socks </strong><b style="margin-left: 67%; position: absolute;"> (19)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=d8b489df08f3179c92e5521c&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Shells </strong><b style="margin-left: 67%; position: absolute;"> (3)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=744db9866d0fbcb42d7c3da8&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Cpanels </strong><b style="margin-left: 67%; position: absolute;"> (12)</b>
                                                    </a>



                                                </div>
                                            </div>

                                            <div class="sub-cat">
                                                <div class="sidebar-categories__item parent">


                                                    <a href="/search?fcats[]=3d65cc292d35cb561b626d88&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                        <strong class="mx-2">Other </strong><b style="margin-left: 67%; position: absolute;"> (51)</b>
                                                    </a>



                                                </div>
                                            </div>




                                        </div>

                                        <hr>

                                        <div class="sidebar-categories__item parent">
                                            <a href="/search?fcats[]=1899e6354798484de1bfad50&amp;cats=2&amp;s_quick=1" class="cat-name">
                                                <strong class="mx-2">Other Listings</strong><b style="margin-left: 67%; position: absolute;"> (161)</b>
                                            </a>
                                        </div>

                                        <hr>


                                    </li>
                                </ul>

                                <div class="mx-auto flex gap-2 items-center">

                                    <label for="cats2" class="bg-abacus2 hover:bg-abacus block 2xl:hidden px-2 py-0.5 text-white w-max rounded">Close</label>

                                </div>

                            </div>

                        </div>

























                    </form>


                    <!-- LATEST NEWS -->

                    <div class="bg-white border-solid border-[1px] border-border rounded-md m-0 !p-2 text-abacus hover:!border-abacus2 overflow-hidden flex flex-col gap-2" style="max-height: 400px;">
                        <h1 class="infobox flex items-center justify-start 2xl:justify-center !text-abacus !bg-white 2xl:!bg-abacus 2xl:!text-white !rounded-md !border-none py-2">
                            <i class="gg-layout-list mr-2"></i>
                            Latest News
                        </h1>

                        <div class="flex flex-col gap-1 py-1 pr-1 overflow-y-auto">

                            <p class="rounded-md my-0 bg-abacus bg-opacity-[5%] px-3 py-1 hover:bg-opacity-10">
                                <span class="text-abacus"><b>February 14, 2023</b></span>
                                <br><span class="text-black leading-tight">HAPPY NEW YEAR TO ALL ABARIANS! We are very happy to start 2023 with all of you.</span>
                            </p>
                            <p class="rounded-md my-0 bg-abacus bg-opacity-[5%] px-3 py-1 hover:bg-opacity-10">
                                <span class="text-abacus"><b>June 26, 2022</b></span>
                                <br><span class="text-black leading-tight">Ladies and gentlemen of Abacus Market. Abacus is open for everyone again after one week of updates and any pending transaction was processed including any pending withdrawal.</span>
                            </p>
                            <p class="rounded-md my-0 bg-abacus bg-opacity-[5%] px-3 py-1 hover:bg-opacity-10">
                                <span class="text-abacus"><b>January 07, 2022</b></span>
                                <br><span class="text-black leading-tight">This is first update of the year. We improved search functions, fixed all reported bugs and improved the user interface of certain pages.</span>
                            </p>
                            <p class="rounded-md my-0 bg-abacus bg-opacity-[5%] px-3 py-1 hover:bg-opacity-10">
                                <span class="text-abacus"><b>November 04, 2021</b></span>
                                <br><span class="text-black leading-tight">Network infrastructure and re-branding update completed successfully.</span>
                            </p>
                            <p class="rounded-md my-0 bg-abacus bg-opacity-[5%] px-3 py-1 hover:bg-opacity-10">
                                <span class="text-abacus"><b>October 08, 2021</b></span>
                                <br><span class="text-black leading-tight">Upgrade completed: Easier captcha added, fixed all reported bugs, improved forum stability. More improvements coming soon. </span>
                            </p>
                            <p class="rounded-md my-0 bg-abacus bg-opacity-[5%] px-3 py-1 hover:bg-opacity-10">
                                <span class="text-abacus"><b>October 02, 2021</b></span>
                                <br><span class="text-black leading-tight">We are working on a better captcha. It will be implemented during the following days.</span>
                            </p>
                            <p class="rounded-md my-0 bg-abacus bg-opacity-[5%] px-3 py-1 hover:bg-opacity-10">
                                <span class="text-abacus"><b>September 28, 2021</b></span>
                                <br><span class="text-black leading-tight">Market is now live.</span>
                            </p>


                        </div>
                    </div>

                    <!-- END LATEST NEWS -->



                </div>

                <!-- end search -->

                <!-- cont products search weclcome and messages -->

                <div class="w-full 2xl:w-[calc(100%-300px)] 6xl:w-[2600px] 7xl:w-[calc(100%-300px)] mx-auto flex flex-col gap-2">

                    <div class="w-full flex flex-col gap-1">
                        <p class="disablejs">We highly recommend that you disable Javascript when viewing the marketplace for better security.</p>

                        <span>
                <style>.disablejs { display: none !important; }</style>
            </span>






                    </div>


                    <!-- STARTS DREAD NOTIFICATION -->

                    <form class="m-0 rounded pl-2 pr-5 flex items-center gap-0.5 border-solid border-1 relative overflow-hidden" style="background-color: #1a1e23;color:#fff !important;border-color: #9b59b6;" action="" method="post">

                        <div class="dread bg-center bg-no-repeat h-10 w-10 flex-shrink-0" style="background-size: 24px 24px;background-color: #9b59b6;"></div>

                        <div class="text-xl font-bold pr-1">Dread</div>

                        <p class="font-bold border-solid border-0 border-l border-white pl-1.5" style="color: #fff;">Follow us on Dread. We would love to hear your feedback and discuss ideas about the market with you. As a vendor you can make your own announcement in our community&nbsp;<a href="http://g66ol3eb5ujdckzqqfmjsbpdjufmjd5nsgdipvxmsh7rckzlhywlzlqd.onion/d/AbacusMarket" target="_blank" class="rounded hover:scale-105 inline-flex items-center px-2 font-bold" style="background-color: #9b59b6; color:#fff !important">/d/AbacusMarket <span class="scale-75"><i class="gg-chevron-right" style="color: #fff !important;"></i></span> </a></p>

                        <!-- button -->
                        <input type="hidden" name="csrf_token" value="3cb77627b8ee5e779bb85.9NGiEhxGPMqPkEvFDhbxG7tG_r8cmIHQ6kmt-lO_Nlk.sJD0X1IwUa3t5BjxanK5Y9wW09JJyde8uArfgynRbC2wq_tkcSdF-M3dIA">
                        <button class="absolute text-white bg-transparent border-none top-0 right-0 hover:scale-105" title="CLOSE ANNOUNCEMENT" name="close_dread">
                            <i class="gg-close"></i>
                        </button>

                    </form>





                    <div class="text-xs personal-msg">
                        <b>Personal phrase:</b> <i class="underline">doublea</i>
                        <div class="italic text-white">The sentence above will only protect you against basic phishing sites, not against reverse proxy phishing sites.</div>
                    </div>


                    <div class="m-0 rounded px-2 flex items-center gap-0.5 border-solid border-1 relative overflow-hidden bg-gradient-to-tr from-yellow-300 to-yellow-400 border-gray-900" action="" method="post">

                        <div class="bg-center bg-no-repeat h-10 w-10 flex-shrink-0 relative flex flex-col items-center justify-end">
                            <div class="flex items-center justify-center gap-1 mx-auto mb-0.5 animate-pulse2">
                                <div class="bg-red-700 w-0.5 h-2 -rotate-12 -mb-1"></div>
                                <div class="bg-red-700 w-0.5 h-2"></div>
                                <div class="bg-red-700 w-0.5 h-2 rotate-12 -mb-1"></div>
                            </div>
                            <div class="w-1/2 bg-gradient-to-r from-red-700 via-red-500 to-red-700 h-4 rounded-t-full overflow-hidden animate-pulse2"></div>
                            <div class="w-9/12 bg-gray-600 rounded-t-md h-2"></div>


                        </div>

                        <div class="font-bold pl-1.5 text-xs py-1 " style="color: rgb(31, 41, 55) !important;">
                            We will provide private mirrors to those trusted users who have setup their XMPP/Jabber with a <i class="underline">clearnet domain </i>
                            &nbsp;<a href="/editprofile#set-pr-l" target="_blank" class="rounded hover:scale-105 inline-flex items-center px-2 font-bold bg-gray-900 text-white">Configure Jabber <span class="scale-75"><i class="gg-chevron-right" style="color: #fff !important;"></i></span> </a>
                            <br>
                            * Example Clearnet Domain: @xmpp.is <br>
                            ** This notification will hide when a clearnet Jabber/XMPP is configured <br>
                            *** We will contact you only from AbacusNotifications@xmpp.is
                            <label for="jabber-verfify" class="rounded hover:scale-105 inline-flex items-center px-2 py-0.5 font-bold bg-gray-900 text-white">Verify</label>

                            <!-- POP SIGNED VERIFY JABBER MSG -->

                            <input type="checkbox" class="hidden absolute peer" id="jabber-verfify">

                            <label for="jabber-verfify" class="anim anim-FadeIn h-full w-full fixed hidden peer-checked:block top-0 left-0 bg-abacus bg-opacity-50 z-40">&nbsp;</label>

                            <div class="hidden text-center fixed w-11/12 xl:w-auto peer-checked:block space-y-2 top-[75px] left-1/2 -translate-x-1/2 z-50 bg-white p-4 rounded-md">

                                <h2 class="text-abacus text-center">PGP Signed Jabber Address / Verify it MANUALLY</h2>

                                <pre class="select-all font-normal text-black  !bg-back mx-auto  border-solid 2xl:overflow-x-auto overflow-y-scroll border-border2 hover:border-abacus2 border rounded-md px-1 2xl:px-4 py-2 leading-tight text-[9px] !text-justify 2xl:text-xs" style="height: calc(100vh - 350px);max-height: 700px;">-----BEGIN PGP SIGNED MESSAGE-----
Hash: SHA512

AbacusNotifications@jabber.sk is the ONLY clearnet Abacus jabber
-----BEGIN PGP SIGNATURE-----

iQKTBAEBCgB9FiEEM8QH2+dgp1Ag9A6WEmswjqdgKLEFAmP5UNFfFIAAAAAALgAo
aXNzdWVyLWZwckBub3RhdGlvbnMub3BlbnBncC5maWZ0aGhvcnNlbWFuLm5ldDMz
QzQwN0RCRTc2MEE3NTAyMEY0MEU5NjEyNkIzMDhFQTc2MDI4QjEACgkQEmswjqdg
KLHaWA//arQWRIgTRkUDQ2HyA2BWiCWa1Oplr+y0R4OuG5hFd9xx3E9oYYTQ4hvm
PkZxWsekN1TG3yvV0NPLXLuX5xvD74JkOYe9CHr6rWrJDrOc6xaCLkCqwFoxQFbA
tuvYGa+Jxx32ct1ag8I74bI2fcmheKz7bhtz5VD5OMAkByJHgEP3gJBt0Q9w86Ik
+ZTbr/Sz+grqWSC0PnVmRLjdC3aPG/f5C522iOOLibAARdC1XPd+GxHC72blpbor
lQ0vBZKtZQiOuxelTM2pNJIIwbQoRxKmpdQog58dyuo9gU0qerw4FcZKH6OYfr9o
KhJY8MhvGpu1DrPNFBqZt5YDigHalopAEyjA5zoMT/W1jrVjwofg+e6sjp6Kjhhy
LwB7E8Ggrl0G57Tz3lT2m9CGo4nE2hTQHxu8LFeZShiNoYKj22ZvFx5IroPcbC/o
nAHMDnsFrC2sp/YEQneLQiVG7utt1wlBURTjDZlm6fE0q0Dymff0mkkTG593OVos
v5BnJ4pFWxu+d0CfbZfBX8MhNpOHAgsj+G/3IxS/LrnxTIhcZrC8cTeInKrhx/xh
XBiGNRZDXWpLOVF7DLNE61N672GGKytYlVxwedezmWZ+QIT4f+kgVN3CBxz+yWfC
qfPNJG7UtgrlOD8bE+ieWSr0TyVrGy+8fLsLMqJXNJXAqyS5AXo=
=d2fB
-----END PGP SIGNATURE-----</pre>

                                <div class="flex items-center justify-center gap-4 mt-2">
                                    <label for="jabber-verfify" class="px-2 py-0.5 text-xs rounded bg-abacus hover:bg-abacus2 text-white font-bold">Close</label>
                                </div>
                            </div>
                        </div>

                    </div>



                    <!-- FEATURED LISTINGS -->

                    <div class="bg-white border-solid border-[1px] border-border rounded-md px-[5px] py-3 m-0 w-full flex flex-wrap justify-around">

                        <div class="min-w-full mb-[5px] font-bold">
                            <span class="text-[13px] font-bold m-0 uppercase leading-9 flex items-center text-gray-600 rounded-md border-solid border border-border px-3"><i class=" text-xl mr-2">★</i>Featured Listings</span>
                        </div>
                        <br>




                        <div class="border-solid border-[1px] mx-[3px] mb-[3px] rounded-md p-[6px] group !flex flex-wrap justify-between flex-1 text-sm min-w-lg w-11/12 2xl:w-1/3 6xl:min-w-[15%] !border-border !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-[1px] rounded-md px-2 mb-1 py-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold" href="/listing/416dc57d51ee6dd005bd01fc">28 Grams Cocaine (FE Listing)</a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <div class="p-1 w-[8.8em] h-[8.55em] my-1 bg-back border-solid border-[1px] border-border text-center inline-block rounded-md">
                                    <a style="font-size: 14px;" href="/listing/416dc57d51ee6dd005bd01fc">
                                        <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,UklGRoQEAABXRUJQVlA4IHgEAACQEwCdASpGAEYAPpE6mEiloyIhLNGuYLASCWkA1JLyf6tryWslsss/jt79g8h31P4IT95sh6b3bi5cLAtOjWwh+5Y7WpI97Xe9Li7Ol59G2emdW3VMiPogClHYkAq0fPH0/8/agZIa+hE6y8g0V5HqjNNM62L8mA4tBzSTBgLfUvHCXIUU/uJkmoaxVaONIS6x9hpIKcrr+Lrufz7be/VPyyH7UGAA/unOaujqGkJAruAMuuhjdiqUGI+qkuXrq5pJO1NKcSPG5/ZLX2yE3a40zxUdv1q8m3fY7ODm9CRvoQoSj/EK4oxCw8jh+BDlwk7HQyE2m9uZ58MwXxGQwxLQX4F/Kv98meDUqOBJc+NfKwEYc/fBOn55wAAT7q7qbMSk2TXNNv3HiOuQX39sQ/uE4yTP+pcgWOoLbKSJW+PUg7wb/tySY+cwSiiJOPb9JYI+m5GLVkjhdVTxTE5jTeUVzJ22nUcooPz7KSOWzB/8c+5PyTOypgzsd+ifxOxHQhfZAXuePn2Vwf5bWkEabdIXQLA7Sf/+FtYmk7LZFATDP224W5tTi/fdh/YHkA0EEWwQD1bjbLi2N9jRj0JgIF61YMuFXEA4t3IaG9y56/IXMCXK4KQGvVtgQypZhhnStTvaZWzcako1YesPX7bPFlsJRn3liB8wJWqYDAWEsMKpbrXosKA1u3nRwVZEeSlCrgE/hFHnG70L+erO5o6pGl/716axkV4IPvXfhtHYBTbWk2MtygmE3aDwTncFDYAuDV5WL+QZsB5y6mWsSaJK2jGz4fMkoZHV/vHuoYAPpx1SzVviR0dw4prBsDJa+SOaZz2Z0VUHFEobaJJj39glB/C/PR6xazd8KrfcO868ud26DMFSEI3e2UFzR9/xsCDIrtREZIDf0XojSqbMeR8TUiscn3/yLbsSe54F7VBxG1viuOsHxyfCdP+A5UbSR+XLKvfT6hSkiSrEFKTCaiTGPiSHy7a/B/8Oo75Fh1KEhuX1gi+pqPUX8VJZmgV9Z1j8I9NhjzwulBaGXpjUN2ugQ37Tunxe0W3OVU5XrxGl9MrTklHRnydMFnzH5/3HTPJw4FwrbOm9NFJnwAgGqn7NBmkkKYAxYyTfovUAQRQnMztsXCT/edWH5pOSOm2ck/cQ413pshXaA240SLrU7TKbaZ5ZQ9w6ya4JNh2x8wL/devmAX4sbHHwSyBblOtDG42zb1qJvPN7mW5NUyTfVzSN3+99txKwFNNEwdl76BB7K9FHKA7eVxiEtl3Dy/a1OeCpDDIuZN5L1kwtsj5BOJZLusVsCUXpgdLvSdTYSygDonctzwUgonxHTtaeWvKLZgp9BscyNX6NctVjQFooMBqmNspxBzwWtqK3pkj+LWWO6b4UD28CD3iX1daOGJpnRr7U4gGHeqmpmzBUMKziNFgl/KUfJNSVUC6rRx+vCfHvYXh/+2LOpoHASeeoxl6R6GW8qHQEG83l6/7+ZwwaOODsQYCi/f/s3RrZwSFFOrZwBRRhtlYk6aP0LAAA">
                                    </a>
                                </div>
                                <div class="px-2 py-1 flex flex-col gap-1">

                                <span class="text-black text-left !text-xs font-bold">                                    Cocaine
                                </span>

                                    <div class="text-black font-bold text-xs pb-1 border-solid border-0 border-b-[1px] border-abacus flex flex-wrap">Sold by:<a class="ml-1  font-normal" href="/profile/97bb724891ed4bc7bd96c520">CokeMonk (2452)</a></div>



                                    <div class="grid grid-cols-2 w-full text-xs text-abacus divide-solid divide-x divide-y-0 divide-abacus">
                                        <div class="text-center">
                                            <div class="text-black">Feedback</div>


                                            <div class="text-white bg-green w-max mx-auto rounded-md px-1">99.26 %</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-black">Vendor Lvl</div>
                                            <span class="px-2 py-0.5 rounded bg-lvl8 text-white">8</span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 items-center w-full border-solid border-0 border-t-[1px] border-abacus text-xs text-abacus divide-solid divide-x divide-y-0 pt-1 divide-abacus">

                                        <span class="text-sm text-black font-bold  w-full text-center pt-2">USD 691.00</span>

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center group-hover:bg-abacus rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">United States</span>
                                        </div>
                                    </div>





                                </div>
                                <a href="/listing/416dc57d51ee6dd005bd01fc" class="col-span-2 flex items-center justify-center gap-2 hover:gap-0.5 bg-abacus2 hover:bg-abacus text-white text-center rounded text-sm px-4 py-0.5 leading-none">View Product <i class="gg-arrow-right ml-2"></i></a>
                            </div>

                        </div>



                        <div class="border-solid border-[1px] mx-[3px] mb-[3px] rounded-md p-[6px] group !flex flex-wrap justify-between flex-1 text-sm min-w-lg w-11/12 2xl:w-1/3 6xl:min-w-[15%] !border-border !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-[1px] rounded-md px-2 mb-1 py-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold" href="/listing/ca794c0b3ae24281a4f1dad5">PERUVIAN COCAINE (HPLC TESTED 97.2%) - 3.5G</a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <div class="p-1 w-[8.8em] h-[8.55em] my-1 bg-back border-solid border-[1px] border-border text-center inline-block rounded-md">
                                    <a style="font-size: 14px;" href="/listing/ca794c0b3ae24281a4f1dad5">
                                        <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,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">
                                    </a>
                                </div>
                                <div class="px-2 py-1 flex flex-col gap-1">

                                <span class="text-black text-left !text-xs font-bold">                                    Cocaine
                                </span>

                                    <div class="text-black font-bold text-xs pb-1 border-solid border-0 border-b-[1px] border-abacus flex flex-wrap">Sold by:<a class="ml-1  font-normal" href="/profile/d39e4ce543bbb7703649e638">rushrush (214)</a></div>



                                    <div class="grid grid-cols-2 w-full text-xs text-abacus divide-solid divide-x divide-y-0 divide-abacus">
                                        <div class="text-center">
                                            <div class="text-black">Feedback</div>


                                            <div class="text-white bg-green w-max mx-auto rounded-md px-1">100 %</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-black">Vendor Lvl</div>
                                            <span class="px-2 py-0.5 rounded bg-lvl5 text-white">5</span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 items-center w-full border-solid border-0 border-t-[1px] border-abacus text-xs text-abacus divide-solid divide-x divide-y-0 pt-1 divide-abacus">

                                        <span class="text-sm text-black font-bold  w-full text-center pt-2">USD 360.00</span>

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center group-hover:bg-abacus rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">United States</span>
                                        </div>
                                    </div>





                                </div>
                                <a href="/listing/ca794c0b3ae24281a4f1dad5" class="col-span-2 flex items-center justify-center gap-2 hover:gap-0.5 bg-abacus2 hover:bg-abacus text-white text-center rounded text-sm px-4 py-0.5 leading-none">View Product <i class="gg-arrow-right ml-2"></i></a>
                            </div>

                        </div>



                        <div class="border-solid border-[1px] mx-[3px] mb-[3px] rounded-md p-[6px] group !flex flex-wrap justify-between flex-1 text-sm min-w-lg w-11/12 2xl:w-1/3 6xl:min-w-[15%] !border-border !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-[1px] rounded-md px-2 mb-1 py-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold" href="/listing/c330370647ddc80c40b0a32e">100x 30 MG DP Instant Release Adderall For Sale!</a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <div class="p-1 w-[8.8em] h-[8.55em] my-1 bg-back border-solid border-[1px] border-border text-center inline-block rounded-md">
                                    <a style="font-size: 14px;" href="/listing/c330370647ddc80c40b0a32e">
                                        <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,UklGRuAJAABXRUJQVlA4INQJAACQKgCdASpGAEYAPjEUiEMiISEWaZ4gIAMEoAnTKEeS+sfkl+R3yMWPp9NM+UNzb55/6p6xPKv8oT1oebn9kvW3/y/pb+kB1M3Ps+1R/rvNVu8L7f+Hnmr4efJP6t/V/9t6bv8z4K+SPMT5Xvsf1k83/+F1yWoF+QfxL/J/mR7mnnfbmgH/R/4t/w/1f8d3UC7m+Y/4tfIFfj7yjP/T9eP2q93/z//0fUX/7364e4v///ct6Q36M//YX/cyw+bjv9vZnUPxofcaYo+GIM1p0MBvclWwswP8Av9uAS8y87E6Pe2j6pA0UVqQd+eJUMB/stqboRw173F+04tZIp+6j1nStHRt+yDWdG0qtf1hcEhgzn09l8E6dXr2MEYJLIaeTj+/6MJpY7g7zip3CGpkpC9z6hYacSAn1AQVmGfhtVmBK0nXBMYLwrfgoqmn0AI2qjbTv1dDvDIRbPq6FbqAAP78IBxHG8wAvxYS+b0T4IcIPaBNhJQtf7KkJ/I2zQ9eC8UjRMx2i9fS3txVBCRLzMLq/eEkc7Qnk7/G3Ux+OkfpClDkxbXS/0wWIebMmzA///Y3u3AAysvAHQ3wOe//eTUg4c5zx8BvDdslfxjOcveCwyqnALXs7BFgpmdA1F+PITkT6hSyYOMxdGDSleOvFxUF/ZbbJ8CEr3Ws7Hzt3K4rxaGz5LvI9pNrMUQTdVPO2P5X8cFV3O1InqFx7rGm4Cr0t4OYF4fE4jKsOxvWiEJzXhDIUX0LKrZPnnxoFKGqJBXCz9ymoJuovmJk5vnwBQ4P09G7KIAG5RhDTWSq5Ju1E65e27975ehrzciIPD5rv/+TA59bBp+T3Tr/8iP2pvT/D2z4YZcteHvirLyhcAWtS46ooPzhoIB+P9/gWBc6wl+q1aa1QP8gQ8XZiGlNsTEoBO9ZN7ykKPXL/xDgvn2u7f3QgoYH0CORblLor6+Fr5lWy3zTgRWVS8BwRR65YM9FT96otM/NZuD4Xc9Y5v1d1mXgIGdJs4j6d2+O0Y8N1CU6UiAyYkJbBz828xg63jD+L1OYq0t8/yMyYpLbqjZdcW9f4bj8n2E4PABxP4kw7/9IaTs1n72+Egk5AEsOm8ultni7Q7AV+OzD0tRe5rcSQ8A0rjgSBBq+QYLyXbTZK0KGsYX1ti56U8udl+5TLTBY2q6WEnFRy6/dsS8bA/qDolMtlJaTSnU3wylGcuDqKlOvqt2PGmxy77pdaoOiNc/kHt8wmqNE45P6ZYHeeq83um/zf5Ew1sA6C7ugPYUI5dkP44JjtOJ5nMW1Pnftrp2Xh93+y2xykFMICXeHk28frGVl1hOXXMZeF77gXXWe2lzry/iAJywjWVMAWUcYyG8ZnVBy2ajp4UvOO13ZO4jl92ud1U8RHt8nDxNo1AKqkiauckm9+WingiWnyeumuKICLtJycDjUOah5Gl5p9v1O0o9eib3vuJGvdBQ40bm4I9XASsR0DZY5RfpLMSorAwqwMmrJ83RbY98QpzhHhMotGT7UqKHxTL4KCDY8Syc9R8WSvsjoPyTGkDyCMj67S6kySV0NQKKOhJco8Vsg915HuDU61tbqWNGHo2rQcImQyo6e7wxgBWsbc4fCqPFKlhzD21egxVcFm+Xl+mHTzEtBiTrNcbfIv6PZLPWmiy6QVRz3jGOcvVr0l+KBN2SFuA2nvM/IEG6XFw9jO8KptYy9OR6TscQZNyAWWdunrH53wUt1zcU2jz6rcr2zBXIc7U/jv2b4MbA+CP6M0B2FUV9+H+OgvubfAxqqco70822svH/2c9u6Ca5o26llXZuH9jpW5I2IRVSofLbW3RLsae7psAaqGPE985V6hd4Rf2PxO6adyFaubNQ6OxizySCIV1mPOITLoWP09bpREYY1vmmMMuqTqVmcedRQIwq32ipeK03335bBz0hPRqTnrWcqKFN3qViNtnZbi+WcFCzPYdElxSZWCOWaJz0mP7CXpcjdmJpfVgxYTBfoZNSn9YZBYnEWjkN61RGDGoF7ZnwraM2+nWiaZoGKDIQPtZ19Dua8vLmKKtuW6zUN3HCbPKksFY3D27O5jZ/PnuVX3K0q3L1HlZsMgMPs1mYpwC20fdVxTFYKcwUEmtYZm4jqPV3d1rR3mj7SudrVLQN/EN6hn6dd1Op9LtV/MJAVY47B2HU10ElPWNa0Tq2dxNHn53mTBFOVOCRsFtGDuVH4zmp5hlGbrTDa/tQzSau+zswUHgm1z95MharDuZ9Z80Eui7gZ/cSOywge3ekth8tWgdDz1gJaw9L4A1tOTDhowj3nHStbY5G4OHbK3SvGF/EZ5sYWF0WasqQnwiCeUBRsMfctW9KNXFWFWZDlKwwBdsIN3tJ/DU7AEG17QgHbdONftp/no/GEEuZdi85Q39vflR8UzhcRN0sWIG7lAB3Bw/kfW9UiAcMOBXvHfIr4gkMiwYP9q4v0ZguYciLA4zt7mai17naxyBT63FlUTkR1Rznfa841WBiJaueQsgWlEf9XukmF79jA1Qzqb7s1hvyacSRzJDe1LaDQmI69ARRlh+d4ii3CgjTUC+bVtzojdsyvFJkqgGQ2eDKKWeej7tXqTy/6ueJlibGdy9v3i2FkQJTnOcUeE05XqsI1xgvSzjNbWQVgI1PwmFUvC8rMjThgJxgRdCtE8kRlRB7imjV9fw/df/krNP71dmg/s4uXKZtm06fScuPqXuf3XDLGHI83/T6BkAxrfKZ6RQjjGjWiAQMxo6MTrPkUzKjdcczDpj8HxPimmM6POJYqswTKqQK0ereGzn32r11HG5O4n1Sk0yBgKC5hd/5Wlt2/IEfoezCcNvJ86KQd+F/RW6oBLRy9O7Jq/Pk8TcZXm3B7PK4ll6UiarmBcfUaFnw0fRu9rSOAJoHuFTfFI72IJDA1HA3eOyRG2QdZEDcaXLb+6Hgb0QYx9W46t3J/ICwThFkBC7otTDaz/qRU5tOrSswKQ7wkhJyLK3b4iuqKjm8lp3RW5glk/GMC7GlZkG8n1/koKvy/WKoXb6zU6pgAIgoI9X1a5NyjBsDm14/+y9QEKllAwe1KBO+6FKCIBPx3nXg+qdaeg0fpzBba2Jgw7bBWwWBTlfyzp+sH15uiXujsikrBdgGHgEdeASESTL2RBpcyCgTPmAS2WXLrceF/4Dfn7ui/6F0D+s8EDuk7CC/YuaK8uSgza4IIT71z0lbCkxVHHhVpsjjIiQtSojwp4axZVH2TjIR3N8884sODz7ZdYMV9+S2dw6YATMyBbkMSDj84x0/us/q7oYjn7E5D7n5+HwCB+h3fLsFlIT5iZPO5YaBNrsdbf9AmNCG/OHwmwkT2fUOvuvgNWG+wqAAAAA==">
                                    </a>
                                </div>
                                <div class="px-2 py-1 flex flex-col gap-1">

                                <span class="text-black text-left !text-xs font-bold">                                    Adderal &amp; Vyvan...
                                </span>

                                    <div class="text-black font-bold text-xs pb-1 border-solid border-0 border-b-[1px] border-abacus flex flex-wrap">Sold by:<a class="ml-1  font-normal" href="/profile/60b070d35eeabd2663829eb0">Abraxis (129)</a></div>



                                    <div class="grid grid-cols-2 w-full text-xs text-abacus divide-solid divide-x divide-y-0 divide-abacus">
                                        <div class="text-center">
                                            <div class="text-black">Feedback</div>


                                            <div class="text-white bg-green w-max mx-auto rounded-md px-1">100 %</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-black">Vendor Lvl</div>
                                            <span class="px-2 py-0.5 rounded bg-lvl3 text-white">3</span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 items-center w-full border-solid border-0 border-t-[1px] border-abacus text-xs text-abacus divide-solid divide-x divide-y-0 pt-1 divide-abacus">

                                        <span class="text-sm text-black font-bold  w-full text-center pt-2">USD 82.00</span>

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center group-hover:bg-abacus rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">United States</span>
                                        </div>
                                    </div>





                                </div>
                                <a href="/listing/c330370647ddc80c40b0a32e" class="col-span-2 flex items-center justify-center gap-2 hover:gap-0.5 bg-abacus2 hover:bg-abacus text-white text-center rounded text-sm px-4 py-0.5 leading-none">View Product <i class="gg-arrow-right ml-2"></i></a>
                            </div>

                        </div>



                        <div class="border-solid border-[1px] mx-[3px] mb-[3px] rounded-md p-[6px] group !flex flex-wrap justify-between flex-1 text-sm min-w-lg w-11/12 2xl:w-1/3 6xl:min-w-[15%] !border-border !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-[1px] rounded-md px-2 mb-1 py-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold" href="/listing/63a33ca4af053c2adbca1206">[$55] 50x B974 ♥️ PHARMA QUALITY ADDERALL 30 MG ♥️ [IR] OPENING SALE ♥️</a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <div class="p-1 w-[8.8em] h-[8.55em] my-1 bg-back border-solid border-[1px] border-border text-center inline-block rounded-md">
                                    <a style="font-size: 14px;" href="/listing/63a33ca4af053c2adbca1206">
                                        <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,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">
                                    </a>
                                </div>
                                <div class="px-2 py-1 flex flex-col gap-1">

                                <span class="text-black text-left !text-xs font-bold">                                    Adderal &amp; Vyvan...
                                </span>

                                    <div class="text-black font-bold text-xs pb-1 border-solid border-0 border-b-[1px] border-abacus flex flex-wrap">Sold by:<a class="ml-1  font-normal" href="/profile/11e30264c5d9896543194055">addyinc0 (1432)</a></div>



                                    <div class="grid grid-cols-2 w-full text-xs text-abacus divide-solid divide-x divide-y-0 divide-abacus">
                                        <div class="text-center">
                                            <div class="text-black">Feedback</div>


                                            <div class="text-white bg-green w-max mx-auto rounded-md px-1">99.31 %</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-black">Vendor Lvl</div>
                                            <span class="px-2 py-0.5 rounded bg-lvl6 text-white">6</span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 items-center w-full border-solid border-0 border-t-[1px] border-abacus text-xs text-abacus divide-solid divide-x divide-y-0 pt-1 divide-abacus">

                                        <span class="text-sm text-black font-bold  w-full text-center pt-2">USD 70.00</span>

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center group-hover:bg-abacus rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">United States</span>
                                        </div>
                                    </div>





                                </div>
                                <a href="/listing/63a33ca4af053c2adbca1206" class="col-span-2 flex items-center justify-center gap-2 hover:gap-0.5 bg-abacus2 hover:bg-abacus text-white text-center rounded text-sm px-4 py-0.5 leading-none">View Product <i class="gg-arrow-right ml-2"></i></a>
                            </div>

                        </div>



                        <div class="border-solid border-[1px] mx-[3px] mb-[3px] rounded-md p-[6px] group !flex flex-wrap justify-between flex-1 text-sm min-w-lg w-11/12 2xl:w-1/3 6xl:min-w-[15%] !border-border !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-[1px] rounded-md px-2 mb-1 py-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold" href="/listing/3084d6586599b937357d9ca1">**NEW SELLER PROMO** 100x THE BEST B974 30mg ANYWHERE!! FREE SHIPPING</a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <div class="p-1 w-[8.8em] h-[8.55em] my-1 bg-back border-solid border-[1px] border-border text-center inline-block rounded-md">
                                    <a style="font-size: 14px;" href="/listing/3084d6586599b937357d9ca1">
                                        <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,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">
                                    </a>
                                </div>
                                <div class="px-2 py-1 flex flex-col gap-1">

                                <span class="text-black text-left !text-xs font-bold">                                    Adderal &amp; Vyvan...
                                </span>

                                    <div class="text-black font-bold text-xs pb-1 border-solid border-0 border-b-[1px] border-abacus flex flex-wrap">Sold by:<a class="ml-1  font-normal" href="/profile/c08a8b17d13fb0ac290f20a8">Simponis (675)</a></div>



                                    <div class="grid grid-cols-2 w-full text-xs text-abacus divide-solid divide-x divide-y-0 divide-abacus">
                                        <div class="text-center">
                                            <div class="text-black">Feedback</div>


                                            <div class="text-white bg-green w-max mx-auto rounded-md px-1">99.68 %</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-black">Vendor Lvl</div>
                                            <span class="px-2 py-0.5 rounded bg-lvl5 text-white">5</span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 items-center w-full border-solid border-0 border-t-[1px] border-abacus text-xs text-abacus divide-solid divide-x divide-y-0 pt-1 divide-abacus">

                                        <span class="text-sm text-black font-bold  w-full text-center pt-2">USD 85.00</span>

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center group-hover:bg-abacus rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">United States</span>
                                        </div>
                                    </div>





                                </div>
                                <a href="/listing/3084d6586599b937357d9ca1" class="col-span-2 flex items-center justify-center gap-2 hover:gap-0.5 bg-abacus2 hover:bg-abacus text-white text-center rounded text-sm px-4 py-0.5 leading-none">View Product <i class="gg-arrow-right ml-2"></i></a>
                            </div>

                        </div>



                        <div class="border-solid border-[1px] mx-[3px] mb-[3px] rounded-md p-[6px] group !flex flex-wrap justify-between flex-1 text-sm min-w-lg w-11/12 2xl:w-1/3 6xl:min-w-[15%] !border-border !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-[1px] rounded-md px-2 mb-1 py-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold" href="/listing/86dd00819146bfdc71e375d8">US Adderall IR 30mg x25 EXPRESS SHIP</a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <div class="p-1 w-[8.8em] h-[8.55em] my-1 bg-back border-solid border-[1px] border-border text-center inline-block rounded-md">
                                    <a style="font-size: 14px;" href="/listing/86dd00819146bfdc71e375d8">
                                        <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,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">
                                    </a>
                                </div>
                                <div class="px-2 py-1 flex flex-col gap-1">

                                <span class="text-black text-left !text-xs font-bold">                                    Adderal &amp; Vyvan...
                                </span>

                                    <div class="text-black font-bold text-xs pb-1 border-solid border-0 border-b-[1px] border-abacus flex flex-wrap">Sold by:<a class="ml-1  font-normal" href="/profile/28366dc5344cb07192ab5ac8">superwave (1222)</a></div>



                                    <div class="grid grid-cols-2 w-full text-xs text-abacus divide-solid divide-x divide-y-0 divide-abacus">
                                        <div class="text-center">
                                            <div class="text-black">Feedback</div>


                                            <div class="text-white bg-green w-max mx-auto rounded-md px-1">99.62 %</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-black">Vendor Lvl</div>
                                            <span class="px-2 py-0.5 rounded bg-lvl6 text-white">6</span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 items-center w-full border-solid border-0 border-t-[1px] border-abacus text-xs text-abacus divide-solid divide-x divide-y-0 pt-1 divide-abacus">

                                        <span class="text-sm text-black font-bold  w-full text-center pt-2">USD 110.00</span>

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center group-hover:bg-abacus rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">United States</span>
                                        </div>
                                    </div>





                                </div>
                                <a href="/listing/86dd00819146bfdc71e375d8" class="col-span-2 flex items-center justify-center gap-2 hover:gap-0.5 bg-abacus2 hover:bg-abacus text-white text-center rounded text-sm px-4 py-0.5 leading-none">View Product <i class="gg-arrow-right ml-2"></i></a>
                            </div>

                        </div>



                        <div class="border-solid border-[1px] mx-[3px] mb-[3px] rounded-md p-[6px] group !flex flex-wrap justify-between flex-1 text-sm min-w-lg w-11/12 2xl:w-1/3 6xl:min-w-[15%] !border-border !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-[1px] rounded-md px-2 mb-1 py-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold" href="/listing/67effd51e147ae551d205a2f">Mix n Match 2g Disposable - Free Priority Shipping</a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <div class="p-1 w-[8.8em] h-[8.55em] my-1 bg-back border-solid border-[1px] border-border text-center inline-block rounded-md">
                                    <a style="font-size: 14px;" href="/listing/67effd51e147ae551d205a2f">
                                        <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,UklGRioFAABXRUJQVlA4IB4FAACwFQCdASpGAEYAPo08mEelI6KhMfW66KARiUAZqBADHHEnVzi/qf25fmLx1z0AOmDn0HOP70k/tueo7XL5I4KuuY5b/YO+y1PlTv6//qfQB6P2gRUK6VqaewrsRi+Q0A16TNWTj7EwKYCoX1KNP/HDoXkH/r4rSd71Nk3nypKs7uIi1XS41881XIZMk5FabYFVW7WcqYhGe2EEZoekx5OBtxfFbEMzqSk/fmM4uU0UGbTznX54AAD+9yySvdkqQ804YCF4rtOwDW8QV6O/4G/VTa9ylKkPXePLEg+ZvrzKBQt8AcEiLFbB1sAGy7jzaVJqLBFAk2XewNhyC5PQygq7mfH1B3E9sIqgwxyMhgvbN0F09mg/W5OgfWFWgHaNVQRa6ZyTBAUA8e83OEvWOu+Nj7cR/88JwQ3RU8zkBNB9LeKZbTR+FV9bBlFab/YEt+VFzLDBRk9RF2APypI1rGGDA4Kf5gnFwWqC9NudWD3/bjXDAdlkfkS5VV/pbNGrGJ8sELqThd5+g8Y8zZPxbiB/F3VdUbO/pfeeNsqgwHYzY1YpszPVUxpNr2e3rmSzPL/clgzPIvKfZ7RBJcdST8D9SLEekuGiFtmq1j2C9w8Y2bX8u8o4iZF9LK6NVJxhevxJvFbXX8GQGHBZmRms8+o/xF1ei0Yvs1hztANlCKN+AXmHTs61s2a+ckkNEmg1bZysPeXGxkpat+ncBmWOhMTuix9KeDl+odvdSTyFwNEFZUp3x8KiasQTdohFBuVQrdu4ZyXVDWHTlKc7WVhSZMWx9L7yG1phsyTDOmZSLM/qnzTYYIDgeYSlfiJrY2rcsD8vP/8gi7D4+o4sjE+ym2sRuM0FvQPPM3BBUkAg+yij2JhINzK/IG8TOCwHfqPT74Daj3eEF+yJzlhVXd0qY2tBXjC73jGhEjVqUYDWJOE+65gFwtio+7hHId0Kv5FicBN3XMeOdA0oX+Cq/OiNUOTajTTlsjnU4LJq8QWw1J6bQUpKicv+Mh/o891hFcRaA0oopK+1OQf4HfMfc4FQP3vBjq5PYFwHzX50PFogTACubqctEzupyb2B36df2XTN5HhKEMrl7rqIFeAMM9JP1hsUXSBI46RQAewGuC/sMUJhnHulCOh6ddV6jgcYkB5JDywqBRJz5H24x+OxIi2qn3ofmq82Y68XfR2M1YCpY7qLZa1udU9zj9krF4Q7dGdJq9zDAi7GBfaga+bOCgH/z56alpkTOy/DdCnfaeM2rxKrjUkHbU/AeTMeB+lNHWuPRP/4OrGa0GAxqaztomS49/+kJ8Q99dT6GUFW5goCP2kie2U3eOQAfPLTT6G1jUVBJzCBoctjmNDrwhs6c9WxIc38GQ8OIULEZVKXMfrAvA0KWu7TwpocOpAp1kOZv40eNz8oRHrFYiz1aYQW8FxcIkC6QSDaCWOANHwVvAQjrLjULZpvT1mXpo9vSv+x0eRi+9Zg8sVOlM5I1ZiDWzgbfmvqRUQTCToXjVNDDyqod4vaPo70GQEfxtPzEJ0kRbMGCEgaj/ykEVtyzii1OjaR68ThQIS7+D7tkrSVDrZNq2s8lUfFQbPIhFvllfmrOVsd9FqWOjIBDO+97B05QvDNZKr/WeswFGUUOH2wiGuVYLkiFDpu3MdnjayXptUs5k0PjXPXnc9WGZ0p06UA64WgzmxzSvW8xSokDpyHAOfuIhUjRXyQSY8/M762Tt9Z4gEdz5dJB2AE80Gi6rIizgAAAA==">
                                    </a>
                                </div>
                                <div class="px-2 py-1 flex flex-col gap-1">

                                <span class="text-black text-left !text-xs font-bold">                                    Concentrates
                                </span>

                                    <div class="text-black font-bold text-xs pb-1 border-solid border-0 border-b-[1px] border-abacus flex flex-wrap">Sold by:<a class="ml-1  font-normal" href="/profile/31bbe494fe1b7fb5acc4a8c3">777lucky (1279)</a></div>



                                    <div class="grid grid-cols-2 w-full text-xs text-abacus divide-solid divide-x divide-y-0 divide-abacus">
                                        <div class="text-center">
                                            <div class="text-black">Feedback</div>


                                            <div class="text-white bg-green w-max mx-auto rounded-md px-1">100 %</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-black">Vendor Lvl</div>
                                            <span class="px-2 py-0.5 rounded bg-lvl6 text-white">6</span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 items-center w-full border-solid border-0 border-t-[1px] border-abacus text-xs text-abacus divide-solid divide-x divide-y-0 pt-1 divide-abacus">

                                        <span class="text-sm text-black font-bold  w-full text-center pt-2">USD 11.50</span>

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center group-hover:bg-abacus rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">United States</span>
                                        </div>
                                    </div>





                                </div>
                                <a href="/listing/67effd51e147ae551d205a2f" class="col-span-2 flex items-center justify-center gap-2 hover:gap-0.5 bg-abacus2 hover:bg-abacus text-white text-center rounded text-sm px-4 py-0.5 leading-none">View Product <i class="gg-arrow-right ml-2"></i></a>
                            </div>

                        </div>



                        <div class="border-solid border-[1px] mx-[3px] mb-[3px] rounded-md p-[6px] group !flex flex-wrap justify-between flex-1 text-sm min-w-lg w-11/12 2xl:w-1/3 6xl:min-w-[15%] !border-border !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-[1px] rounded-md px-2 mb-1 py-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold" href="/listing/78b8e5f10b4f79d0be7fe599">[$625] 1000x B974 ♥️ PHARMA QUALITY ADDERALL 30 MG ♥️ [IR] OPENING SALE ♥️</a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <div class="p-1 w-[8.8em] h-[8.55em] my-1 bg-back border-solid border-[1px] border-border text-center inline-block rounded-md">
                                    <a style="font-size: 14px;" href="/listing/78b8e5f10b4f79d0be7fe599">
                                        <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,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">
                                    </a>
                                </div>
                                <div class="px-2 py-1 flex flex-col gap-1">

                                <span class="text-black text-left !text-xs font-bold">                                    Adderal &amp; Vyvan...
                                </span>

                                    <div class="text-black font-bold text-xs pb-1 border-solid border-0 border-b-[1px] border-abacus flex flex-wrap">Sold by:<a class="ml-1  font-normal" href="/profile/11e30264c5d9896543194055">addyinc0 (1432)</a></div>



                                    <div class="grid grid-cols-2 w-full text-xs text-abacus divide-solid divide-x divide-y-0 divide-abacus">
                                        <div class="text-center">
                                            <div class="text-black">Feedback</div>


                                            <div class="text-white bg-green w-max mx-auto rounded-md px-1">99.31 %</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-black">Vendor Lvl</div>
                                            <span class="px-2 py-0.5 rounded bg-lvl6 text-white">6</span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 items-center w-full border-solid border-0 border-t-[1px] border-abacus text-xs text-abacus divide-solid divide-x divide-y-0 pt-1 divide-abacus">

                                        <span class="text-sm text-black font-bold  w-full text-center pt-2">USD 635.00</span>

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center group-hover:bg-abacus rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">United States</span>
                                        </div>
                                    </div>





                                </div>
                                <a href="/listing/78b8e5f10b4f79d0be7fe599" class="col-span-2 flex items-center justify-center gap-2 hover:gap-0.5 bg-abacus2 hover:bg-abacus text-white text-center rounded text-sm px-4 py-0.5 leading-none">View Product <i class="gg-arrow-right ml-2"></i></a>
                            </div>

                        </div>



                        <div class="border-solid border-[1px] mx-[3px] mb-[3px] rounded-md p-[6px] group !flex flex-wrap justify-between flex-1 text-sm min-w-lg w-11/12 2xl:w-1/3 6xl:min-w-[15%] !border-border !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-[1px] rounded-md px-2 mb-1 py-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold" href="/listing/c27e3a9f637109750403c0d0">(1000) $0.550ea - Adderall B974 30mg IR (Instant) - FE PROMO</a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <div class="p-1 w-[8.8em] h-[8.55em] my-1 bg-back border-solid border-[1px] border-border text-center inline-block rounded-md">
                                    <a style="font-size: 14px;" href="/listing/c27e3a9f637109750403c0d0">
                                        <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,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">
                                    </a>
                                </div>
                                <div class="px-2 py-1 flex flex-col gap-1">

                                <span class="text-black text-left !text-xs font-bold">                                    Adderal &amp; Vyvan...
                                </span>

                                    <div class="text-black font-bold text-xs pb-1 border-solid border-0 border-b-[1px] border-abacus flex flex-wrap">Sold by:<a class="ml-1  font-normal" href="/profile/585b382048ebf130c8ac1515">addyrus (1934)</a></div>



                                    <div class="grid grid-cols-2 w-full text-xs text-abacus divide-solid divide-x divide-y-0 divide-abacus">
                                        <div class="text-center">
                                            <div class="text-black">Feedback</div>


                                            <div class="text-white bg-green w-max mx-auto rounded-md px-1">99.2 %</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-black">Vendor Lvl</div>
                                            <span class="px-2 py-0.5 rounded bg-lvl8 text-white">8</span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 items-center w-full border-solid border-0 border-t-[1px] border-abacus text-xs text-abacus divide-solid divide-x divide-y-0 pt-1 divide-abacus">

                                        <span class="text-sm text-black font-bold  w-full text-center pt-2">USD 570.00</span>

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center group-hover:bg-abacus rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">United States</span>
                                        </div>
                                    </div>





                                </div>
                                <a href="/listing/c27e3a9f637109750403c0d0" class="col-span-2 flex items-center justify-center gap-2 hover:gap-0.5 bg-abacus2 hover:bg-abacus text-white text-center rounded text-sm px-4 py-0.5 leading-none">View Product <i class="gg-arrow-right ml-2"></i></a>
                            </div>

                        </div>



                        <div class="border-solid border-[1px] mx-[3px] mb-[3px] rounded-md p-[6px] group !flex flex-wrap justify-between flex-1 text-sm min-w-lg w-11/12 2xl:w-1/3 6xl:min-w-[15%] !border-border !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-[1px] rounded-md px-2 mb-1 py-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold" href="/listing/a4e971f9ac6035ef5b008171">Peruvian Cocaine 94.78% 4g</a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <div class="p-1 w-[8.8em] h-[8.55em] my-1 bg-back border-solid border-[1px] border-border text-center inline-block rounded-md">
                                    <a style="font-size: 14px;" href="/listing/a4e971f9ac6035ef5b008171">
                                        <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,UklGRgwEAABXRUJQVlA4IAAEAAAQEwCdASpGAEYAPpE4mEkloyIhLNGuYLASCWUAzdMgGSANn22cvdQRZqnkkfXpLDE05bAUBwvI3MtKF+B2Rr1YUrBLxflcx4ntzKeVefXUV5FrUlW547gEoreGR09edd72wk+03vwh11qfRo4d1fdw6w5QVckOYqmddAgSBYeEZfLSdfRsDqAS6pqSlRQ4VGxZvFSPAP78//Ip5T5DebN2gAD+9CzzJXEy3TRaCdolLCNurPyfnv828jb5bvyOwhutOzANKphEtUWYkLkDYd+iFt6APkgOcPSW/9pH3lA6HN33O5pySv4fOkipJLf0oGbhzvpW0nF0PYlialc+s6YrQUGEdwi2AmxBf0Qui+muEh0b2UwnPhv7Pom62kQKsvg1OZy5Nj7DlVzlrqNZcVXrlEEA1c/9JPHGqnVZ7dElpeevoPZMLAJw+tBRXFXWNuKKa8kNov37acmUeOLT4rMbvfleEKa9KNyXMCUlugbOMFNsNnNycnXn5Zmq+cyXYP6EG2iyv4d0L66Ff+6JzIgD3n0nmb5eoxMQxcbi3Nt8a+tpNTfTOXmPccXJlYM5MEEn5t4nQqnOvQSX4DGop/HxQCtw8rOJWrE7Uzz9F55vqg4dNNXfEjhvyShRWKJuPhIg8CFOkebVHMkyXDc4RX4OXb9AlvxkmN16BCvceWiMRQuzuWCS7cy3VKYP0vF8+Oo0oCY5F3QCosNbPRX/b//HPrOdkeoSif9+oJ0i8z+j2oFJgyyKvZWWneWKFKKbbwvJLjG+X8kyk1RRBnZILsuwa/ZKKu4P4+FNAIcT5VwMaz/OTr2sHyKozqqQqNtR7h0Cka6hdyFE7zdrT6ByuVI7SoGhoZjdbICOOGXLJN+48stlHJAkff3SGU9D5O4a4Db/sSqPFFnYlFs+3Xwkuk/6W8IVqWdcxUP3Z6NDTuxCnDcmbiF4KniRA0GnugJyHfn/8hXBk238oFh4PzAHWD4vvoeE6lfc6gmycLXizG8gmXN9+kgkqDuh5BK97kJIqlcQApcZ4LFKdunTGYqSWsaFSS+fk1++3yemIoMz6hBySSqoqtjwvI4Z++HD9DfKIqeyY0nQaAgj21ZuvIcS5BUkmWQNFCXmIDEqWPP8zCekLw61uXzp2+AXGIw30DabtwtmCC4k4eyjeHskBM187i7VxoSwaf3PC55qqM3Zq47j0mNs0iMXngYPJWPZNCVzTqk1DUsHA0mQRSSRqtW8vRJWnIXc6TGWjk+GF25LS2RDrVJ+bmdsb2KU6xq2HKiGqZX0RnqTXb8q+lLlkhS7XLrdFg9QSFSsGw0RbbcwfmfqbdDJjo4OkHsZBWWy90AI6+tQKS82LNFgmQZa8vUUkQAA">
                                    </a>
                                </div>
                                <div class="px-2 py-1 flex flex-col gap-1">

                                <span class="text-black text-left !text-xs font-bold">                                    Cocaine
                                </span>

                                    <div class="text-black font-bold text-xs pb-1 border-solid border-0 border-b-[1px] border-abacus flex flex-wrap">Sold by:<a class="ml-1  font-normal" href="/profile/ede4b837102723ba84ca9b90">addyexpress (5)</a></div>



                                    <div class="grid grid-cols-2 w-full text-xs text-abacus divide-solid divide-x divide-y-0 divide-abacus">
                                        <div class="text-center">
                                            <div class="text-black">Feedback</div>


                                            <div class="text-white bg-green w-max mx-auto rounded-md px-1">100 %</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-black">Vendor Lvl</div>
                                            <span class="px-2 py-0.5 rounded bg-lvl1 text-white">1</span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 items-center w-full border-solid border-0 border-t-[1px] border-abacus text-xs text-abacus divide-solid divide-x divide-y-0 pt-1 divide-abacus">

                                        <span class="text-sm text-black font-bold  w-full text-center pt-2">USD 113.00</span>

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center group-hover:bg-abacus rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">United States</span>
                                        </div>
                                    </div>





                                </div>
                                <a href="/listing/a4e971f9ac6035ef5b008171" class="col-span-2 flex items-center justify-center gap-2 hover:gap-0.5 bg-abacus2 hover:bg-abacus text-white text-center rounded text-sm px-4 py-0.5 leading-none">View Product <i class="gg-arrow-right ml-2"></i></a>
                            </div>

                        </div>



                        <div class="border-solid border-[1px] mx-[3px] mb-[3px] rounded-md p-[6px] group !flex flex-wrap justify-between flex-1 text-sm min-w-lg w-11/12 2xl:w-1/3 6xl:min-w-[15%] !border-border !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-[1px] rounded-md px-2 mb-1 py-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold" href="/listing/befaf908ce2dfc103b2a706d">German Pharmacy S-Isomer Ketamine PURE 4g</a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <div class="p-1 w-[8.8em] h-[8.55em] my-1 bg-back border-solid border-[1px] border-border text-center inline-block rounded-md">
                                    <a style="font-size: 14px;" href="/listing/befaf908ce2dfc103b2a706d">
                                        <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,UklGRhgDAABXRUJQVlA4IAwDAADwEgCdASpGAEYAPpE8l0mloyGhMLFJWLASCWUAyMxhb/6n91tKZ5PMdsUNnUkhx6hj+UqWyIq1vDYeaeKlP3wocN4mlSJAG0uAGq0LkFR157djpi/kL6yQpP9eLD8qos7EJv4XeUuZkZM876Ni6hw6dqdnvV1hiS8SL9qZ7G+TuVB+6E+Xo+Yw46sEIsRX+pZVpIabpHFANHxs2riNgbAAAP7sElS5LsoPoLa/ZHORj7Evrsb7fDvAVKn35HQhdhj5C+2zbHkw3PxOGkQCf6RNho8xjyuseySX3HG1G1SCm5QW7z/2eHkBZBMwlf38HdRCgCE7MK1YBjmV1mlJLW85IH/NkCrnDEzICJxKC5UvyoBhbOTguqn5xwhrXtjmO5YkzgWyyNuLDlVXHpRBbL7MFnOFxCQ53+b/BKxlx6mchulnXlxUJk986lmfw1fj7MuIbMy7/O4u+clH0NU/s+379quw5WVQlMNmOWkDFhw2uNV1LUEoq1iEGXraJ+DcnIa2nPhyAIZqElvxi74zQLfSPObYHhfE2NGPX1hLI5qP/4+Tbyyo1m+9s8ohkD1X3yrhMfLYV1Rd6/SN/Y8TpldnlNFk/BpjgcxTrTMWB6A1EAD5qBII8CbPTPcnsOIdgod/dh9SAw3uuExdmi69C6ZKEDspgbTKRVFcaNmWm0wXLC7eollG2vt6WmeIwYGn2Xp54rZoBRpdidf/3uuPrEOOPjPRPa111zGTBYqdWg/Jgm4aQ4uyZCwx6aPeKfuDAVnkBbk/Xhd+3+n+pCqrNppyEvAy2SX4t+sdPF16CnN+aDF9V940p+PX6LV6SV+bHgi58SVNA0896yLREhT6LzSrDhunMTBqpcTh3E1hpWKKjDGURwkOenL29KLQdsBTnSMvBeM3vaIrwzSMxtQZo2f+UPsrCuaszn+YRse4IPBvkspx7T6HJ5y2YHOJzpcWPswSlcDiNNnv+8DMIbBlr+XXlFfErJqalM8vPVIRWmpwAMkiDiQMgCOir5URzHc38Q9qC/VfVsDaRfgAAAA=">
                                    </a>
                                </div>
                                <div class="px-2 py-1 flex flex-col gap-1">

                                <span class="text-black text-left !text-xs font-bold">                                    Ketamine
                                </span>

                                    <div class="text-black font-bold text-xs pb-1 border-solid border-0 border-b-[1px] border-abacus flex flex-wrap">Sold by:<a class="ml-1  font-normal" href="/profile/ede4b837102723ba84ca9b90">addyexpress (5)</a></div>



                                    <div class="grid grid-cols-2 w-full text-xs text-abacus divide-solid divide-x divide-y-0 divide-abacus">
                                        <div class="text-center">
                                            <div class="text-black">Feedback</div>


                                            <div class="text-white bg-green w-max mx-auto rounded-md px-1">100 %</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-black">Vendor Lvl</div>
                                            <span class="px-2 py-0.5 rounded bg-lvl1 text-white">1</span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 items-center w-full border-solid border-0 border-t-[1px] border-abacus text-xs text-abacus divide-solid divide-x divide-y-0 pt-1 divide-abacus">

                                        <span class="text-sm text-black font-bold  w-full text-center pt-2">USD 101.00</span>

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center group-hover:bg-abacus rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">United States</span>
                                        </div>
                                    </div>





                                </div>
                                <a href="/listing/befaf908ce2dfc103b2a706d" class="col-span-2 flex items-center justify-center gap-2 hover:gap-0.5 bg-abacus2 hover:bg-abacus text-white text-center rounded text-sm px-4 py-0.5 leading-none">View Product <i class="gg-arrow-right ml-2"></i></a>
                            </div>

                        </div>





                        <div class="border-solid border-[1px] mx-[3px] mb-[3px] rounded-md p-[6px] group !flex flex-wrap justify-between flex-1 text-sm min-w-lg w-11/12 2xl:w-1/3 6xl:min-w-[15%] !border-border !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-[1px] rounded-md px-2 mb-1 py-1 flex items-center flex-wrap">
                                <div class="text-xs text-center font-bold mx-auto text-abacus">FREE SLOT</div>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <div class="p-1 w-[8.8em] h-[8.55em] my-1 bg-back border-solid border-[1px] border-border text-center inline-block rounded-md">
                                    <a style="font-size: 14px;" href="/featured">
                                        <div class="picture picture-freeslot group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md"></div>
                                    </a>
                                </div>
                                <div class="px-2 py-1 flex flex-col gap-1">
                                    <div class="grid h-full w-full items-center text-sm text-abacus text-center">
                                        <div class="font-bold flex flex-col items-center gap-1">
                                            <b class="font-bold text-xl">★</b>
                                            <br>Featured Product Coming Soon
                                        </div>
                                    </div>
                                </div>
                                <div href="" class="col-span-2 flex items-center justify-center gap-2 hover:gap-0.5 bg-abacus2 hover:bg-abacus text-white text-center rounded text-sm px-4 py-1.5 leading-none">Coming Soon </div>
                            </div>

                        </div>



                    </div>



                    <!-- RANDOM LISTINGS -->

                    <div class="bg-white border-solid border-[1px] border-border rounded-md px-[5px] py-3 m-0 w-full flex flex-wrap justify-around">
                        <div class="min-w-full mb-[5px] font-bold">
                            <span class="text-[13px] font-bold m-0 uppercase leading-9 flex items-center text-gray-600 rounded-md border-solid border border-border px-3"><i class=" gg-database mr-2"></i>Random Listings</span>
                        </div>
                        <br>




                        <div class=" border-solid border-[1px] !border-border mx-[3px] my-[5px] rounded-md p-[6px] text-sm !grid grid-cols-1 grid-rows-[50px,135px] min-w-lg flex-1 w-11/12 max-h-[210px] 2xl:w-1/3 6xl:min-w-[15%]  !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-0 border-b-[1px] px-2 mb-1 pb-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold inline-block" href="/listing/f4a87fbfdd59d39f52b6331e">1337 Crew Database<span class="w-[fit-content] rounded text-black !text-xs font-bold"> -                                         Dumps
                                    </span></a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <a class="w-[109px] h-[109px] m-auto" href="/listing/f4a87fbfdd59d39f52b6331e">
                                    <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,UklGRrQAAABXRUJQVlA4IKgAAADQBgCdASpGAEYAPm0yk0ekIyGhK5VYgIANiWkAAGpZ7i64TpGY+XnycCaOGGOnCkz+Gxy7npbu6qPVeDuaogAA/vVrCRb0ke6bHrAFtEu3X+25Xii7a8IE9otJBJkAjCDGqLoT8oblimH+aX9TliGnn+L3nDFJ2IE2ocnWu6RCBUyBUe2tzlP8RRyI8azBovBl5iIsGqsQ23AXc51008eFXsEpjS0AAAA=">
                                </a>
                                <div class="px-2 flex flex-col text-abacus justify-evenly">
                                    <div class="font-bold text-xs pb-1 border-solid border-0 py-[2px] border-b-[1px] flex flex-wrap text-black">Sold by:<a class="ml-1 font-normal" href="/profile/12c2270e580fe2f526e3bd44">Topvendor (108)</a></div>






                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Vendor Lvl: <span class="rounded bg-lvl3 text-white px-1 py-[2px]">3</span></div>

                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Feedback:                                     <span class="text-xs font-bold px-0.5 rounded" style="background-color:#fff;color:green !important;">97.83 %</span>
                                    </div>

                                    <div class="grid grid-cols-[auto,auto,30px] gap-1 items-center justify-items-center">

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-xs text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">Worldwide</span>
                                        </div>

                                        <div class="text-sm underline text-center text-black font-bold leading-none">USD 69.00</div>

                                        <a href="/listing/f4a87fbfdd59d39f52b6331e" class=" bg-abacus2 text-white  hover:bg-abacus  rounded text-sm w-max px-[2px] py-[4px] leading-none self-center"><i class="gg-chevron-right"></i></a>
                                    </div>

                                </div>
                            </div>

                        </div>







                        <div class=" border-solid border-[1px] !border-border mx-[3px] my-[5px] rounded-md p-[6px] text-sm !grid grid-cols-1 grid-rows-[50px,135px] min-w-lg flex-1 w-11/12 max-h-[210px] 2xl:w-1/3 6xl:min-w-[15%]  !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-0 border-b-[1px] px-2 mb-1 pb-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold inline-block" href="/listing/716e372ac62a00cc27e68087">5g Original Dutch MDMA 85% [FREE SHIPPING]<span class="w-[fit-content] rounded text-black !text-xs font-bold"> -                                         MDMA
                                    </span></a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <a class="w-[109px] h-[109px] m-auto" href="/listing/716e372ac62a00cc27e68087">
                                    <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,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">
                                </a>
                                <div class="px-2 flex flex-col text-abacus justify-evenly">
                                    <div class="font-bold text-xs pb-1 border-solid border-0 py-[2px] border-b-[1px] flex flex-wrap text-black">Sold by:<a class="ml-1 font-normal" href="/profile/e28b80eca92bbbf4c43f8930">DutchEnterprises (155)</a></div>






                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Vendor Lvl: <span class="rounded bg-lvl2 text-white px-1 py-[2px]">2</span></div>

                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Feedback:                                     <span class="text-xs font-bold px-0.5 rounded" style="background-color:#fff;color:orange !important;">82.86 %</span>
                                    </div>

                                    <div class="grid grid-cols-[auto,auto,30px] gap-1 items-center justify-items-center">

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-xs text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">Germany</span>
                                        </div>

                                        <div class="text-sm underline text-center text-black font-bold leading-none">USD 41.81</div>

                                        <a href="/listing/716e372ac62a00cc27e68087" class=" bg-abacus2 text-white  hover:bg-abacus  rounded text-sm w-max px-[2px] py-[4px] leading-none self-center"><i class="gg-chevron-right"></i></a>
                                    </div>

                                </div>
                            </div>

                        </div>







                        <div class=" border-solid border-[1px] !border-border mx-[3px] my-[5px] rounded-md p-[6px] text-sm !grid grid-cols-1 grid-rows-[50px,135px] min-w-lg flex-1 w-11/12 max-h-[210px] 2xl:w-1/3 6xl:min-w-[15%]  !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-0 border-b-[1px] px-2 mb-1 pb-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold inline-block" href="/listing/472f78f4fc71684d3b56a16f">2022 GUIDE Carding ATOM  [WORKING]<span class="w-[fit-content] rounded text-black !text-xs font-bold"> -                                         Fraud
                                    </span></a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <a class="w-[109px] h-[109px] m-auto" href="/listing/472f78f4fc71684d3b56a16f">
                                    <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,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">
                                </a>
                                <div class="px-2 flex flex-col text-abacus justify-evenly">
                                    <div class="font-bold text-xs pb-1 border-solid border-0 py-[2px] border-b-[1px] flex flex-wrap text-black">Sold by:<a class="ml-1 font-normal" href="/profile/5e8f55aac55428c37ddb7435">Tuts4ever (1760)</a></div>






                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Vendor Lvl: <span class="rounded bg-lvl2 text-white px-1 py-[2px]">2</span></div>

                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Feedback:                                     <span class="text-xs font-bold px-0.5 rounded" style="background-color:#fff;color:green !important;">96.13 %</span>
                                    </div>

                                    <div class="grid grid-cols-[auto,auto,30px] gap-1 items-center justify-items-center">

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-xs text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">Worldwide</span>
                                        </div>

                                        <div class="text-sm underline text-center text-black font-bold leading-none">USD 0.99</div>

                                        <a href="/listing/472f78f4fc71684d3b56a16f" class=" bg-abacus2 text-white  hover:bg-abacus  rounded text-sm w-max px-[2px] py-[4px] leading-none self-center"><i class="gg-chevron-right"></i></a>
                                    </div>

                                </div>
                            </div>

                        </div>







                        <div class=" border-solid border-[1px] !border-border mx-[3px] my-[5px] rounded-md p-[6px] text-sm !grid grid-cols-1 grid-rows-[50px,135px] min-w-lg flex-1 w-11/12 max-h-[210px] 2xl:w-1/3 6xl:min-w-[15%]  !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-0 border-b-[1px] px-2 mb-1 pb-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold inline-block" href="/listing/236698549e80a86d8b7c6ba9">Ketamine 1g thespecialk<span class="w-[fit-content] rounded text-black !text-xs font-bold"> -                                         Ketamine
                                    </span></a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <a class="w-[109px] h-[109px] m-auto" href="/listing/236698549e80a86d8b7c6ba9">
                                    <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,UklGRoYDAABXRUJQVlA4IHoDAACQFQCdASpGAEYAPjEWiUMiISEVLP0sIAMEsoBmRjInLJdKQOz0hrwD8ZJ6gHiPEAejn8R4UvrQfj/Mlf4b+IegB0EpqEescJRO2cYhxsbRd7wXeWSwUdJkzpiTUZYn32frUuE5v7lytNEGPVU0Z5/6LzKVQDS8hCW9N+/9SsVBl3RlCqXI1QVrOcpdEBbs83A6AKaumccmDqDSteLHcl03d6h2aTFPHbTiNOGQKy6g/kI0FSPOAP7vw9AO+psLpOvPgpt2fW489l/ARacfEu3sBHvTMgLUIYJqYqgWZdD1UQk4rEHhmxwzSpV+LEwsIC/sLpoZxN+KazNfqzdlG6va3M9v6hmjQeOOg3errglU00BPKqYLBfpuw3y4FQmvzWsCmsFBZPTWZAjOrORgGfiYsZ0VBDxdzR6i4Ycxofj2MXC5c/wBqLE2TbZY7opb4326f9a3r7ac+9H+iX9wiiczW3hlQglXs/bJCobQqjrbxNBT1avgWk0V5RBwJXEsmA0sRxv7sl5P0XEqaB9QU6nA6wiA6nym5dPi1L2R0+S/4pmw5VdDETWlS+iz/w83huJuk8e2va86lb7YtTp7qOKbFPf4wFc4COlyee7fzOwaLWW5udhAt9ICeSdPWVndOkkzZmA9389KjbgcmcsE0G5ExzAIdGyZMp25R+i45hF8v4LnCy/Ynq1KrhBOhndJeVzN9yO49jzGvHCFyXolP7NJsoU1k+lX5qqpzB4IpdLxjP3AtAp/D2hnG5PDy5sJSUKj1SuUwC2IWCvKJmuO2lliFOUkfMw2yBbBy60+vn0EA/Qvvm2j9zw/ALCtAkOPYu6eT71iUPP9kDeJR46a3ps4zpAtIaKz4H8K6s/p7qOHq7zLI/CGnMsBL22yb1vjZI29mWhkY3wQTc388VRSCV0c/WUTrylfv2L1f4nCu0rn7v9Dh1Rec/SdyZkHEKfp0GlSCYX2xM92ljEV5DGzZItq6ydfY2db1s9RTMf6pbIVM0K+F81Q7NF3cEhz2fgg0wOJM4sx9V0k9lqv4ZNTNc3Az0sOn+EzofcK2E6MdJoFHP9WuxAcLwEFJ32OytaXcq6L/k9h8o0tZePTKyhOxieVHZV+9usDOqfHwfwo/2iNBvMI9kHN9Cco/IixQxCOYpTQBTw2PK/XhbwTdXM4ZfVYGSXAfRJHUoAAAA==">
                                </a>
                                <div class="px-2 flex flex-col text-abacus justify-evenly">
                                    <div class="font-bold text-xs pb-1 border-solid border-0 py-[2px] border-b-[1px] flex flex-wrap text-black">Sold by:<a class="ml-1 font-normal" href="/profile/1482484e7409352a0397e5c6">thespecialk (74)</a></div>






                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Vendor Lvl: <span class="rounded bg-lvl4 text-white px-1 py-[2px]">4</span></div>

                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Feedback:                                     <span class="text-xs font-bold px-0.5 rounded" style="background-color:#fff;color:green !important;">97.73 %</span>
                                    </div>

                                    <div class="grid grid-cols-[auto,auto,30px] gap-1 items-center justify-items-center">

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-xs text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">Australia</span>
                                        </div>

                                        <div class="text-sm underline text-center text-black font-bold leading-none">USD 78.00</div>

                                        <a href="/listing/236698549e80a86d8b7c6ba9" class=" bg-abacus2 text-white  hover:bg-abacus  rounded text-sm w-max px-[2px] py-[4px] leading-none self-center"><i class="gg-chevron-right"></i></a>
                                    </div>

                                </div>
                            </div>

                        </div>







                        <div class=" border-solid border-[1px] !border-border mx-[3px] my-[5px] rounded-md p-[6px] text-sm !grid grid-cols-1 grid-rows-[50px,135px] min-w-lg flex-1 w-11/12 max-h-[210px] 2xl:w-1/3 6xl:min-w-[15%]  !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-0 border-b-[1px] px-2 mb-1 pb-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold inline-block" href="/listing/bbc5df4552cbef8fea692be0">Optimum.net Premium Account | LIFETIME |<span class="w-[fit-content] rounded text-black !text-xs font-bold"> -                                         Accounts &amp; Bank...
                                    </span></a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <a class="w-[109px] h-[109px] m-auto" href="/listing/bbc5df4552cbef8fea692be0">
                                    <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,UklGRvgDAABXRUJQVlA4IOwDAACwFQCdASpGAEYAPjEYikOiIaEQpgAgAwS0gAnI/PT9+/VzJbfWPxs/Lr35/yXOAepn+cfjJ+XXGFSAeR98A/uv5Mf17zqv3LkMfxX+h/mH6gHCZ/z35c/3b3Pv7X9Xv2k9xnx9/jPcI/mn8V/0f6of8/+9///6M+qb/TYrT9P7eNd7EYaIBOCV3tZXhbTOtosd2ZfvhLhO+wTd8uj/ARBAeVRmxm+VfRFKvAD14aczOCrrkPHuAAD+/q9luJ5YMI33jII9kOHL/cn1ZZ6Zh9UQh2INtweqdA7StMRMYAClHEVzDyz4DtMwfMQkWKTk3MBrlc2ivfgIgnKVRPilHH//mkykteh6G9XDA6fgyf5GYuyXxScBzq/4ivZwg5efSI/Cs+ksxjNiCMDBIvKzy3HxmGRRFl5yRHKAky/CWv/nbfEvgMVfdjgMu9HHkf3nAu0bn+JJkWwxLM/fs2xIW6AeB3HAAOPkre/BayG7Ez+j9uGAQeEo4zNPyUkAbVNSTE1vm8QNVB7GVf64NIFrNwrAgDsh/GqhtrbhN0Ta/NDK4xzKyrMK0lUkYuqfVHIqrMKPGbJ5VgPIE/gzPnDhqvSbKh9ycWLhNUvA9fsqHRdbSb/510QqlHne6/iEoPClKqyxptTutXWPPJRaNk35qGgTHPh+q0+ZXg1AD1ooEOcxGNEKgxE5goLuuFRz3/0wopE+B93De0tAyERG5Fs6//OVg7B//pQLCv3YqTZ+x/JcjDrzmRliEqIbDuZ641JTjw2burP35bADd+EHvhjilLq4US4/wzqV0+QJ/3J1VaMlhFnP77RFGxTBr6+HxGFhxXf7TDuyzoTn5Z/8unqQtgxY+kWpkO4IFs5hpVYzcuAcKeYgnmB1RC6xUtIwhGXm64+6sXbBUu/MIUMz/64tCTX05//8AvnkvuaL/EK9H/QPUu7yUm5+6mtREez/GwUWBdWBiuwFhGryTEaujR904L7iWHHH1Wf0Zm1Ir8kWM/Qjc/4+NyfANYBjbx5JF0UFPTEfab//hJsG9LTVhOiqK9V/EMWcvAPgmo8KdlLthWfl4Hme7pfzWdahrArZyOP3xMjYvoTx1DN5IMJ2LE0vzC7/vivgl/Oig0hwhSap552xbkrE42cWML44Xpyf/w0ymYxx/nAv6ndDBo1pMK59ftQYvYfNfFuE52uus7TOvvv94O0XvkZK8g8JhlOan3//hEPzzbvpupm8X3SBmvv/854//8db/rAfyfvjJ4t/lv9h/4OT/aA/hB89/gAWSUAOKnuT9LO1CIAOUsDeTjouWIZd//UGqF2ysI93qBa7Trh6IRUR8ZnmsOoyfgAAAA==">
                                </a>
                                <div class="px-2 flex flex-col text-abacus justify-evenly">
                                    <div class="font-bold text-xs pb-1 border-solid border-0 py-[2px] border-b-[1px] flex flex-wrap text-black">Sold by:<a class="ml-1 font-normal" href="/profile/531c6d3898e7507bc7783ae4">bulkversion (616)</a></div>






                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Vendor Lvl: <span class="rounded bg-lvl1 text-white px-1 py-[2px]">1</span></div>

                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Feedback:                                     <span class="text-xs font-bold px-0.5 rounded" style="background-color:#fff;color:orange !important;">86.92 %</span>
                                    </div>

                                    <div class="grid grid-cols-[auto,auto,30px] gap-1 items-center justify-items-center">

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-xs text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">Worldwide</span>
                                        </div>

                                        <div class="text-sm underline text-center text-black font-bold leading-none">USD 12.00</div>

                                        <a href="/listing/bbc5df4552cbef8fea692be0" class=" bg-abacus2 text-white  hover:bg-abacus  rounded text-sm w-max px-[2px] py-[4px] leading-none self-center"><i class="gg-chevron-right"></i></a>
                                    </div>

                                </div>
                            </div>

                        </div>







                        <div class=" border-solid border-[1px] !border-border mx-[3px] my-[5px] rounded-md p-[6px] text-sm !grid grid-cols-1 grid-rows-[50px,135px] min-w-lg flex-1 w-11/12 max-h-[210px] 2xl:w-1/3 6xl:min-w-[15%]  !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-0 border-b-[1px] px-2 mb-1 pb-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold inline-block" href="/listing/fd38cc87d57678e991af89d7">US Alprazolam Xanax Bars *V2090* x300 EXPRESS SHIP<span class="w-[fit-content] rounded text-black !text-xs font-bold"> -                                         Other
                                    </span></a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <a class="w-[109px] h-[109px] m-auto" href="/listing/fd38cc87d57678e991af89d7">
                                    <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,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">
                                </a>
                                <div class="px-2 flex flex-col text-abacus justify-evenly">
                                    <div class="font-bold text-xs pb-1 border-solid border-0 py-[2px] border-b-[1px] flex flex-wrap text-black">Sold by:<a class="ml-1 font-normal" href="/profile/28366dc5344cb07192ab5ac8">superwave (1222)</a></div>






                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Vendor Lvl: <span class="rounded bg-lvl6 text-white px-1 py-[2px]">6</span></div>

                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Feedback:                                     <span class="text-xs font-bold px-0.5 rounded" style="background-color:#fff;color:green !important;">99.62 %</span>
                                    </div>

                                    <div class="grid grid-cols-[auto,auto,30px] gap-1 items-center justify-items-center">

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-xs text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">United States</span>
                                        </div>

                                        <div class="text-sm underline text-center text-black font-bold leading-none">USD 384.00</div>

                                        <a href="/listing/fd38cc87d57678e991af89d7" class=" bg-abacus2 text-white  hover:bg-abacus  rounded text-sm w-max px-[2px] py-[4px] leading-none self-center"><i class="gg-chevron-right"></i></a>
                                    </div>

                                </div>
                            </div>

                        </div>







                        <div class=" border-solid border-[1px] !border-border mx-[3px] my-[5px] rounded-md p-[6px] text-sm !grid grid-cols-1 grid-rows-[50px,135px] min-w-lg flex-1 w-11/12 max-h-[210px] 2xl:w-1/3 6xl:min-w-[15%]  !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-0 border-b-[1px] px-2 mb-1 pb-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold inline-block" href="/listing/d35e328d84e7583206c1a00d">24g crack pure<span class="w-[fit-content] rounded text-black !text-xs font-bold"> -                                         Crack
                                    </span></a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <a class="w-[109px] h-[109px] m-auto" href="/listing/d35e328d84e7583206c1a00d">
                                    <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,UklGRo4EAABXRUJQVlA4IIIEAABQGwCdASpGAEYAPjEUiEMiISEVKQcMIAMEs4Bp5jUoaq4NeLeqsmuvNPynRY+qfbTdAb3f9L5BbgTGKdz7IGf6T8u/UA7QHmW/4H3Ae5l/2/o9zhP/O/Q73HegAFdHxJXx3YJu3EFr3NsEOID6hYSL1BVN+N3J5tATEOHswyn2YNEueO3ETGAgfnb15UmXazlQlYbES0gGBO8vBst6LbFg5iNxDY9m0GNRO24khHrxImMNVutCkOXLzo3Ih9L8erBJnjOCtQKMOq1ekhW6ArDUKNRQsI5Z/umDLERkX1YzznypAAD+8koNAeqRM5tAvdV4P39hsou8IT71LUL1CF0/4eeXZS1XSz2QvKc5iKe+LoujHlNYdhU3D7gDhOax7azNO9EYnPSmrEWVIxcZzfyUiOxTvdeCdUfUhzVp6NiMfyi6tiH6VpPFLLmN+Zkd1tGFrxTJOSSB/kWG2IrDPl8+TabJ6GfX1qjTVmfZ2Q//BnszO19XpSfCMUAGP/7j/nx36LH7aL8JNdnLxy8JqOGjYAWubv6oMznNK41zFRbaei7dzrJsyz45ztiEl5i/9tAtc2it8QSvZTjO0ZI+4qnL8/1M4NnEwSkRcGDjwWEuS/5X342KojtRG2CpeYm7xLXJAu6ZMqfqQZ/3ipTH8iEL+3UEj+B4e22SXi5hbu9Jx2CLhn+Cf8w8uQs1fTsmTbmM2ZVQKlRVV6sg8A2VfNRRE2+ztFGaGXBtP8r5r2vzd6D6KSLPZ8pvHABR4CUpl3J/0mY8aSX/6ZhGFs33pYw+5o8lbmVwldy/+cUF64x8jsL+mz1yAycv4PUq36IA//dQJ9Ll2Yl5cvBbny0OYDIxid26yZOZ/d0+GpE+c/F6KNZPn0Ap9TRvBxr7GlfFRWnezZ2MODhI62llifKXytbKGmMwOT7kW+5EzXa3OBd+pcvU2O9kSRsu7Zkkhqp7zk/+F43lqQIBTXe4D/AknMFgpCWfh3P4do+HHRDBGAQAnG25Z5eQs8NzmtpJJtTVCs/LRwj6IfqKk212mZtZoJYeHvig6yIdFzojheiG1NNnPeIr58OT3ESZfWb6xnhKFlb5ClwZboKz+HJmCAWqR9NdQCjYeVJ5wko3YYBax295VgS4PKVWd/TeYpxhWhJN8+j+D+OxT/yxAscSdVF39V5XibN6j5YfkHlGC0GBiheIgeeqNeN9o5MsGX+z2Tc8hvsQfSGHyAEmKIsNWRbJK9eLp/bEL85RB9lzjegssP6HMDIXmNAABQTZysIP8tKMpgliXlS6QO8N920Mhr087oly5eX70DKZl9reldxfFBQGwYrAXxyhqNaYCv6iXUgQK2JUPVpAlQj1jSxPt9eG7AZQowioy7MIxDCdVjVQtu9XURRwi6QF5GwYie1yaqrf3Yfy93AOeafy6nov/g7xaYP2xIaWGUOxPKy6rIedf8TcqkDB6gm+IR20lpsowJEsXa+Ex3VWjoLibVZWqOorgjGqxBC4yM/mD/gSiKIdj8+1AGjwGPafQzmK9qbJVCvUAAAAAA==">
                                </a>
                                <div class="px-2 flex flex-col text-abacus justify-evenly">
                                    <div class="font-bold text-xs pb-1 border-solid border-0 py-[2px] border-b-[1px] flex flex-wrap text-black">Sold by:<a class="ml-1 font-normal" href="/profile/e5d13e4fa1e2cf4c60cfa06f">tebow (638)</a></div>






                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Vendor Lvl: <span class="rounded bg-lvl3 text-white px-1 py-[2px]">3</span></div>

                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Feedback:                                     <span class="text-xs font-bold px-0.5 rounded" style="background-color:#fff;color:orange !important;">86.4 %</span>
                                    </div>

                                    <div class="grid grid-cols-[auto,auto,30px] gap-1 items-center justify-items-center">

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-xs text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">United States</span>
                                        </div>

                                        <div class="text-sm underline text-center text-black font-bold leading-none">USD 750.00</div>

                                        <a href="/listing/d35e328d84e7583206c1a00d" class=" bg-abacus2 text-white  hover:bg-abacus  rounded text-sm w-max px-[2px] py-[4px] leading-none self-center"><i class="gg-chevron-right"></i></a>
                                    </div>

                                </div>
                            </div>

                        </div>







                        <div class=" border-solid border-[1px] !border-border mx-[3px] my-[5px] rounded-md p-[6px] text-sm !grid grid-cols-1 grid-rows-[50px,135px] min-w-lg flex-1 w-11/12 max-h-[210px] 2xl:w-1/3 6xl:min-w-[15%]  !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-0 border-b-[1px] px-2 mb-1 pb-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold inline-block" href="/listing/e0936ef397f931fe488053b2">28G OF 80s Lab Tested Crack Cocaine (UK2UK)<span class="w-[fit-content] rounded text-black !text-xs font-bold"> -                                         Crack
                                    </span></a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <a class="w-[109px] h-[109px] m-auto" href="/listing/e0936ef397f931fe488053b2">
                                    <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,UklGRpgFAABXRUJQVlA4IIwFAAAQHQCdASpGAEYAPjEWiUMiISETOZ7MIAMEoAzfPfQ+fFeblYf8zxrR4+wfIlzEUyAeVrTleNblh9j+13p31Vt6n+n/Lz1D3Cnei2tTs73esgp/uPTz+AcJX/veZx/2frB6j/mH0Dvx19xDqcf0APZGRyzEEnAKibK4+lgxPez1AjgCQ31D7kmmkrjxnU07HSmnR/2/ZizYqY0CnqkIbol8YAdMJIhxpUzSbjJw+h2E2v9AAPAmDWyh7wVLmf5hhqhe7Mvw5KGKBwfVEyU3h+URYMp1v9ngzLLiyHQ7+uF5CqOi6nkstVcV25JgQKUAqOhAAP7737Kgn/9zK3D0Nf9STnpJl0h+et/z+5Kysweors9ZMC3rRLl+19ZrIX7NhN5+m1+UAP/8cKcl7i0E1///kkpa7NWQeTQ2XfusOEl8Cf/oyccX5URrK7qJsy/E8NRBrf9gEjj7rUS3wZ9pk4KDmjulqdAg7L+hTc0/N8cVprGgZs+ORGtC2rLajYW4M8GQrafHGtSRB+aV0LP46rPjSYzsr6jzbc3qYtdRwvJAHlD0uZKlN2t3UKPwrzjoJH18y04hpaJTXcpD5Z29UnI6jQPuxpl6KeiqcAlUqgrWRQKym4N0vWu4UVrHviwqNtH5wyx8h08aP5zCh9Er99NUVqFmx9+VqzcHw8vIUbDBvwqYwQ7mgKP8/nURqnaoDx1rSTP6jITLasfGeobe4T/6KRG4irMYtDCoWXcdYjGGTyVNpda1EcZPnhAlPKr93YrXSssAHoNu8GljBBTBBb8HI8094++Hb3jKlrQmCyVRXFZJauMHzAjPtFx+X57tIfJP8v4gH2aXUkDjO9G4YLe82AW1r/pSws99H7t8TuPcBt4naF3sQFcX1OOucnYLf4oqW+ntd2xxFmS0i2ZedduWj0Grt07LZ5r04V/MudBPY3Q5giIgh5clgWLk6Z5vuDcGUl/fyLxxk9Fiu1Wc91pcjmBMjrYtvAHWNw1tih4umpFwDYVMsT+69xCrN67Xa+UP3/XvaW2L2uzQ/yhXd88Gh64yf68rqVS28WKvV59UttjP2OkM0hl7O4mpTo/E/Qkpk7Klau8I654Kc6/uOBCn16ySRspoQLo7CjppVWf63C/pAHSa94FIgkwMV0F5DHGDcT/1c8C5KkXsiZ2hGI2LslNkMjowG9t8ZabakC9gVemAbk07GN1lBOO9O0H5dXRL5FjXdr1AXnk07Nk8t/g+mOjbAcjytymBaDsFLPPV4Qbz/ygK/7SKBxt8xsrdnREV3Ssx1unfTynqYsN9VfosI+uAnv8DGerF1w+0xm4if4SYZFzfY63JS/40OiYl/454Wsq1ZdIUWZmbugOygGtifVPO9dktI9gZqBkpVPlEtRjn5TegLRjHKrID2PgxTqt2w6EDYCbBKtDvrZcmi7NXR4m5YqqpyY0vEjBj7U/f10hYqmK0rbbBMBmPnwJS6vLXYFiwA+U+D3tyn3etN6Ga/D4FmxxkyzsotjJHrS0o34i3X2J8pMkouKS0BmbnlMgwDGZFsO9MyiZwSaucrWll//xNO5kxeFkNBGS8Kl5+9O1qixLF0lYMqcEIFWxz1xw6cHXUFyo8tQcdycDCHs9TY6/p0fKGSP2dEXQNVWOiVbk3erT596+yhnIvyBZ+iU5vez58nxpDVP1nE86g97asfzdJEuZEAqnY00vtIJ2ggDMkQBs3Np3Tc+YkSg2y/sY3ECu169U127GGvDsb/5rCPr4cPKTtNub53jNjMhiR/f9z90Vk+mj+vXeYqwy1V7oFF/D5TNzilWjdQQy9c0NR/5xZoyVQCRbtNggXVRUH6FFlh4NNTkCu9YNhXxaFLGMpNhUz3Y4Y2+Tm72YQFlIWiLfrypmq4iAKAAAA">
                                </a>
                                <div class="px-2 flex flex-col text-abacus justify-evenly">
                                    <div class="font-bold text-xs pb-1 border-solid border-0 py-[2px] border-b-[1px] flex flex-wrap text-black">Sold by:<a class="ml-1 font-normal" href="/profile/22650da2a2b8bc8f7e46689b">ParcelForce (62)</a></div>






                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Vendor Lvl: <span class="rounded bg-lvl2 text-white px-1 py-[2px]">2</span></div>

                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Feedback:                                     <span class="text-xs font-bold px-0.5 rounded" style="background-color:#fff;color:green !important;">100 %</span>
                                    </div>

                                    <div class="grid grid-cols-[auto,auto,30px] gap-1 items-center justify-items-center">

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-xs text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">United Kingdom</span>
                                        </div>

                                        <div class="text-sm underline text-center text-black font-bold leading-none">USD 1876.36</div>

                                        <a href="/listing/e0936ef397f931fe488053b2" class=" bg-abacus2 text-white  hover:bg-abacus  rounded text-sm w-max px-[2px] py-[4px] leading-none self-center"><i class="gg-chevron-right"></i></a>
                                    </div>

                                </div>
                            </div>

                        </div>







                        <div class=" border-solid border-[1px] !border-border mx-[3px] my-[5px] rounded-md p-[6px] text-sm !grid grid-cols-1 grid-rows-[50px,135px] min-w-lg flex-1 w-11/12 max-h-[210px] 2xl:w-1/3 6xl:min-w-[15%]  !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-0 border-b-[1px] px-2 mb-1 pb-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold inline-block" href="/listing/195e6d1034e7211625cd1539">HOW TO HACK FTID<span class="w-[fit-content] rounded text-black !text-xs font-bold"> -                                         Fraud
                                    </span></a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <a class="w-[109px] h-[109px] m-auto" href="/listing/195e6d1034e7211625cd1539">
                                    <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,UklGRrgEAABXRUJQVlA4IKwEAABwGACdASpGAEYAPjEUiEKiISEYWxUAIAMEsoBpyjGBVKA22brQOWA+GWstdIZ7Bys6wG+P/aeSHUBZDP834M9Un/wO+kkDfEA4Ufkcf+Plc+ePQR/cj2AOq0/QBKNvMBIeHFvw2FTVmi2mO5j26wXdt3xGqQWgE4Fc51LpEPFrwFqQSDK5OUrvL0PNYD6u3RG9jLwoKIQzp9dhO39ti0ozF58CpfxJEafagex/a1OgHbxdiuMDg65OT7eQsqXXSRu3avzpSCu8fPxaCoAA/v74QFSDGoneaox8ox4PJY23B8MdV298eBVdRrheAP8HcqWw1uKf/30j+/LtIjk72Npw9mJrc6Yiq+fW3YRnkevhpXMkOq+cyz2eBM8Nu47s2dqszxjzqv57/74HU/Zf+iBe+Jwat/TTHSdjRmGY2MUqIaTzZd/S2unf9WrrKYmHQmIBue+tM7VsxfgWdM56WQJuqmMOWum07pdgescAz6QVNcgs5EiaWDGrfjcCpr52UgilZh13j8X3s6KLuiPHCgT01OG/NMOor2gfRHDw1PuYPPnNaZ0bT4H9/nEgKlKtTdCBu+R8nKExz/TXB4Aev+/1/AoBfj3L7fD9kjewc8bwb+ddY35anHy3qkNCOXZpKySlOPCTbqixtN3u8RIlLpYZlUjOoPtUMCd63s4MlRtbMgbOqhITPqfSXhsxMuJt3ghgzdgFAJ2gfnYIrPa46vaYygzqzyUkYZkVnHq9Q7/minH2QqLPzt8q/HOlvw9SmH9rNVYw9FEdKTN4uVO0XWdJlvevR6rTS6Z6Cswck1ABAhMpLzYBqGgOaxQazvOUuiEDG39kefE4kdHmQ7TS/sbfNO5hmAQg1Nv0Q3jn1rsX62JlCVr56UkDFvZ57GSxZevO9pAkH18P2e8pwlKrfnUP2710g7gEhf/OpGX8VqWt7ZTzDrs7xHDRCncP2pbwJ9T+34PF7yivo1ail6Ui8dQuMMSJvROJpmcketFo5M9v94RD2xnyLtUuR7A/RT2ymJ/M3P7hmcFN4HxzQGMPonpkAthgB6jb4uW8/qID71phZv3+NfGOto/RG3FOXcc20m3DgP1wQCgbBQnqRApcE/XWc05W2aI2nlgqTmE8k7hLLc3Xc1XtCLmAOOBf1mkHzyYgEXr/KEfZxWVKiRZiDbEZwzhL/xUWfSvDhlNTst6XUEHy4u1SXgrMsRziaA3sTrbo79ugj/NVR7uMAgTHR//M27/GEDg9V+xeSHl9HrCQ8fcUes0n+X2kCqAn88RVcu8sBJ7XlaMtqiU6EXEwa9/sX+RpQ9zABbL8FMa4RTEInqn0VkFOjWtDt+JVZ2QdyMRxUoCZo7Q4bT6+3IdWUgxtnlBjwvam7v5zqD1oaBX1ThANithjAax6mVyLrwrKxjXgNs426Ju99AGbwx9NHv4njobbMacXZpFvhJUJ69n0lROpG0eyd4DP32/0sGbfh8GX9efK1sQK46zDBevBrjqwbqKNrQoRhKW7VU5NfQWPWfRpbqXOzbmO55wQu0uzjECZC2FfxZOzpZynnR8KViRt5XbPueFCQRwf+GSmJiUC6guVkCwpTSgLUtYAAA==">
                                </a>
                                <div class="px-2 flex flex-col text-abacus justify-evenly">
                                    <div class="font-bold text-xs pb-1 border-solid border-0 py-[2px] border-b-[1px] flex flex-wrap text-black">Sold by:<a class="ml-1 font-normal" href="/profile/8a60575b46b1e41e44ff5501">socialwarrior (1753)</a></div>






                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Vendor Lvl: <span class="rounded bg-lvl3 text-white px-1 py-[2px]">3</span></div>

                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Feedback:                                     <span class="text-xs font-bold px-0.5 rounded" style="background-color:#fff;color:green !important;">90.27 %</span>
                                    </div>

                                    <div class="grid grid-cols-[auto,auto,30px] gap-1 items-center justify-items-center">

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-xs text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">Worldwide</span>
                                        </div>

                                        <div class="text-sm underline text-center text-black font-bold leading-none">USD 1.99</div>

                                        <a href="/listing/195e6d1034e7211625cd1539" class=" bg-abacus2 text-white  hover:bg-abacus  rounded text-sm w-max px-[2px] py-[4px] leading-none self-center"><i class="gg-chevron-right"></i></a>
                                    </div>

                                </div>
                            </div>

                        </div>







                        <div class=" border-solid border-[1px] !border-border mx-[3px] my-[5px] rounded-md p-[6px] text-sm !grid grid-cols-1 grid-rows-[50px,135px] min-w-lg flex-1 w-11/12 max-h-[210px] 2xl:w-1/3 6xl:min-w-[15%]  !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-0 border-b-[1px] px-2 mb-1 pb-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold inline-block" href="/listing/6fe0960f4bed751dfa163bb7">50 x ECSTASY, 250mg, UK-UK (BEST QUALITY/PRICE)<span class="w-[fit-content] rounded text-black !text-xs font-bold"> -                                         Pills
                                    </span></a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <a class="w-[109px] h-[109px] m-auto" href="/listing/6fe0960f4bed751dfa163bb7">
                                    <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,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">
                                </a>
                                <div class="px-2 flex flex-col text-abacus justify-evenly">
                                    <div class="font-bold text-xs pb-1 border-solid border-0 py-[2px] border-b-[1px] flex flex-wrap text-black">Sold by:<a class="ml-1 font-normal" href="/profile/8a92d1df7835e579e9025934">RoyalMail (95)</a></div>






                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Vendor Lvl: <span class="rounded bg-lvl3 text-white px-1 py-[2px]">3</span></div>

                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Feedback:                                     <span class="text-xs font-bold px-0.5 rounded" style="background-color:#fff;color:green !important;">100 %</span>
                                    </div>

                                    <div class="grid grid-cols-[auto,auto,30px] gap-1 items-center justify-items-center">

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-xs text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">United Kingdom</span>
                                        </div>

                                        <div class="text-sm underline text-center text-black font-bold leading-none">USD 63.04</div>

                                        <a href="/listing/6fe0960f4bed751dfa163bb7" class=" bg-abacus2 text-white  hover:bg-abacus  rounded text-sm w-max px-[2px] py-[4px] leading-none self-center"><i class="gg-chevron-right"></i></a>
                                    </div>

                                </div>
                            </div>

                        </div>







                        <div class=" border-solid border-[1px] !border-border mx-[3px] my-[5px] rounded-md p-[6px] text-sm !grid grid-cols-1 grid-rows-[50px,135px] min-w-lg flex-1 w-11/12 max-h-[210px] 2xl:w-1/3 6xl:min-w-[15%]  !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-0 border-b-[1px] px-2 mb-1 pb-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold inline-block" href="/listing/7ce7c40ec74c3d966646a941">S-4 ANDARINE , 50 MG - NORDIC FUSION<span class="w-[fit-content] rounded text-black !text-xs font-bold"> -                                         Steroids
                                    </span></a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <a class="w-[109px] h-[109px] m-auto" href="/listing/7ce7c40ec74c3d966646a941">
                                    <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,UklGRjYEAABXRUJQVlA4ICoEAABQFwCdASpGAEYAPjESh0KiIQx9txYQAYJZwDTdkAKjQ+f8Y+rCvl7+dwt30vwA6Dvex8/zzPiEfEr4Yv0ge53MH+gB0IBhYYUjs88a4V94DJNR6C+3smE8MqCmm/ySnAXljSB1LWwrT4yLo9lw5CXgaMrWepuA/n2KYZ32zbI7gT8tt8vyydwLLSH57dA/ooiACPfUEPq0x4lrcj31djEEyI0hvP/i4eFWLDcGavdkosc79QSrwe88QdFaktBsMuzdxHAA/v7kSW5alqAo3PB48POvq2eAVjFvuBHT7yvhFDDAZ20lexW5vNh4zTpLS5O3n+BWKpc8bAxuX7efiC4njxSEANbcTsiZgPU+i3GJ9qFi2PpkBpfm3ZhH4UUEraMVmmdZHL7UPe34FItW7OQgcAznf1prkmf+qFp6+S0ukIRDyERJ3MLXFjYoyPumBBDNexfJPyYje7txBbuoP7h+4+Qqz+h95RXfklucuD3064TfGpqO+0JCjCn4//ASrNzCnyDZMd0hfLWdIrTBcideO6DOOf8Y7F+csZV4VUGEv6/hLS/anT7izuqZ7zriX/XV6vHsJICppP8WIgxHEzBNlD28UWr+KHNnEhCQhNz8Pm2sRPYsoxw8aDVYh9OmWwcdYniw2hON9ktjSQAmv1fXNrvNemzP16yWXfWav9bB+speUk+dY3yfUbdgIKtAEU9iMT/uJQ5iUKazef/j1l34KWon1Haai4lRkf0saaL5d9hX+6zojimhW54X2HUv7JHPX59RqMGp4XvlcTStJFyUlDG3Ijjbex5H4Z9x4yXxZIMSLcZmNyfvyGRhynU3mqCOddCkKmGj9xYpBw7Lx3pPVTYGZ8p4RJlWXktqtOtNoR4gdPpdKZV3KkkK0WXOoks7IDndNFrkY5APIg+b7FsA01dQ54d7FW8Q9pLaOUPXCXg1yTxOn+QIhZNM+zp26o/iRs2YTWQb0U/pj/h1NHFQBXY/iY+WBQmnItLNdHwGI1BYjUkO0MjQGqMvhldxlEUolDeb7GGfi4fuQTfcFptfignWvGQuFxOGR0EEf/yuMMI1nxZAvkWswqzoQjwarJvZW/4DMfkws77oq3HtBhi8BKEIv3ugQ8XwNDQQOuu0Y32fT1Iz3Vb2pdimWs7s5yjITCuf4Sogf8bf+G2n545DzUsKX9xzWjpnAoMhuNyUL7l+m6hMgGe7bYm+JyHc2BS/MWxs6IzcMA/f8g1f1hm0A85HJb466dcV/PW+V790JvCzc/EaPs93ucbYiUZ+oArl0eWxzwhihHBPekHKFz5f4Y3QjBVTQVsyam2JplAEWZtKR3A/JnYiEKjNNvc7+qOuevw7ry2qchOo6GTJR7KDQTvVgvrNVv0FAvS/I+aFaPoL3a/esJ85Z7CjNpwB/9UgKQvJGsydyAAA">
                                </a>
                                <div class="px-2 flex flex-col text-abacus justify-evenly">
                                    <div class="font-bold text-xs pb-1 border-solid border-0 py-[2px] border-b-[1px] flex flex-wrap text-black">Sold by:<a class="ml-1 font-normal" href="/profile/356a52ba8e18245d79206b11">Buyroid (2)</a></div>






                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Vendor Lvl: <span class="rounded bg-lvl1 text-white px-1 py-[2px]">1</span></div>

                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Feedback:                                     <span class="text-xs font-bold px-0.5 rounded" style="background-color:#fff;color:green !important;">100 %</span>
                                    </div>

                                    <div class="grid grid-cols-[auto,auto,30px] gap-1 items-center justify-items-center">

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-xs text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">Canada</span>
                                        </div>

                                        <div class="text-sm underline text-center text-black font-bold leading-none">USD 83.00</div>

                                        <a href="/listing/7ce7c40ec74c3d966646a941" class=" bg-abacus2 text-white  hover:bg-abacus  rounded text-sm w-max px-[2px] py-[4px] leading-none self-center"><i class="gg-chevron-right"></i></a>
                                    </div>

                                </div>
                            </div>

                        </div>







                        <div class=" border-solid border-[1px] !border-border mx-[3px] my-[5px] rounded-md p-[6px] text-sm !grid grid-cols-1 grid-rows-[50px,135px] min-w-lg flex-1 w-11/12 max-h-[210px] 2xl:w-1/3 6xl:min-w-[15%]  !bg-white hover:!bg-hover hover:!border-abacus2">

                            <div class="w-full border-solid border-gray-200 border-0 border-b-[1px] px-2 mb-1 pb-1 flex items-center flex-wrap">
                                <a class="text-xs font-bold inline-block" href="/listing/38b47914fbb7d1073c07cccc">FIRE--30pills PRESSED PERCOCET 10/325 10mg<span class="w-[fit-content] rounded text-black !text-xs font-bold"> -                                         Pills
                                    </span></a>
                            </div>

                            <div class="w-full grid grid-cols-[8.8em,auto]">
                                <a class="w-[109px] h-[109px] m-auto" href="/listing/38b47914fbb7d1073c07cccc">
                                    <img class="group-hover:scale-110 w-[7.8em] h-[7.8em] inline-block mx-auto my-0 rounded-md" src="data:image/webp;base64,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">
                                </a>
                                <div class="px-2 flex flex-col text-abacus justify-evenly">
                                    <div class="font-bold text-xs pb-1 border-solid border-0 py-[2px] border-b-[1px] flex flex-wrap text-black">Sold by:<a class="ml-1 font-normal" href="/profile/5b2a7801137e81768e2a8f79">sexman66 (1074)</a></div>






                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Vendor Lvl: <span class="rounded bg-lvl6 text-white px-1 py-[2px]">6</span></div>

                                    <div class="text-xs border-solid border-0 py-[2px] border-b-[1px] text-black">Feedback:                                     <span class="text-xs font-bold px-0.5 rounded" style="background-color:#fff;color:green !important;">96.87 %</span>
                                    </div>

                                    <div class="grid grid-cols-[auto,auto,30px] gap-1 items-center justify-items-center">

                                        <div class="text-center flex flex-col items-center justify-center">
                                            <div class="text-xs text-black">Ships From:</div>
                                            <span class="px-0.5 py-0.5 max-w-[fit-content] mx-1 flex items-center rounded bg-abacus2 text-white text-[9px] leading-none break-normal font-bold">United States</span>
                                        </div>

                                        <div class="text-sm underline text-center text-black font-bold leading-none">USD 215.00</div>

                                        <a href="/listing/38b47914fbb7d1073c07cccc" class=" bg-abacus2 text-white  hover:bg-abacus  rounded text-sm w-max px-[2px] py-[4px] leading-none self-center"><i class="gg-chevron-right"></i></a>
                                    </div>

                                </div>
                            </div>

                        </div>











                    </div>



                </div>



            </div>
        </div>




        <div class="w-full border-solid border-border border-[1px] bg-white rounded-t-md mt-2 px-2 py-2 flex flex-col 2xl:flex-row justify-between gap-1">

            <div class="flex flex-wrap items-center w-full hover:!border-abacus2 border-solid border-[1px] border-border rounded-md  p-1">
                <a class="text-abacus text-sm font-bold px-2 flex items-center" href="/">
                    <i class="gg-home"></i>
                </a>


                <i class="gg-chevron-right text-abacus opacity-30"></i>

                <a class="text-abacus hover:text-white hover:bg-abacus rounded text-sm font-bold px-2 py3" href="#">Home</a>

                <i class="gg-chevron-right text-abacus opacity-30"></i>

            </div>

            <div class="border-solid border-0 border-l border-border hover:border-abacus2">

            </div>

            <!-- UTC TIME -->

            <div class="flex items-center justify-center w-full 2xl:w-[fit-content] hover:!border-abacus2 border-solid border-[1px] border-border rounded-md h-[38px] py-1">
                <span class="text-abacus w-max text-sm font-bold px-2">30.12.2024 14:19 (UTC)</span>
            </div>

        </div>
    </div>
</div>







</body>
</html>
