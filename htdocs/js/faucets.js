var bs_faucets = 
{
    balance: function()
    {
        $('.balance').each(function(i)
        {
            var span = this;
            var chain = $(span).attr('data-chain');
            var address = $(span).attr('data-address');
            $.fn.blockstrap.api.balance(address, chain, function(results)
            {
                var balance = (parseInt(results) / 100000000).toFixed(8);
                $(span).text(balance);
            });
        });
    },
    checks: function(timeout)
    {
        if(typeof timeout == 'undefined')
        {
            timeout = 30000;
        }
        setTimeout(function () 
        {
            
        }, timeout);
    },
    check: function()
    {
        bs_faucets.balance();
        bs_faucets.qrs();
    },
    claim: function()
    {
        $('.request-coins').on('submit', function(e)
        {
            e.preventDefault();
            var tx = false;
            var form = this;
            var bs = $.fn.blockstrap;
            var button = $(form).find('button[type="submit"]');
            var chain = $(form).attr('data-chain');
            var email = $(form).find('input[name="email"]').val();
            var code = $(form).find('input[name="code"]').val();
            var address = $(form).find('input[name="address"]').val();
            var return_address = $(form).find('input[name="return"]').val();
            var hidden = $(form).find('.toggle-box');
            if(email)
            {
                if(code && !address)
                {
                    var chain_name = $.fn.blockstrap.settings.blockchains[chain].blockchain;
                    bs.core.modal('Warning', chain_name + ' address required in order to send coins.');
                }
                else
                {
                    $(button).addClass('loading');
                    if(code)
                    {
                        $.fn.blockstrap.api.unspents(return_address, chain, function(unspents)
                        {
                            if($.isArray(unspents) && blockstrap_functions.array_length(unspents) > 0)
                            {   
                                var fee = $.fn.blockstrap.settings.blockchains[chain].fee * 100000000;

                                var inputs = [];
                                var available_balance = 0;
                                $.each(unspents, function(k, unspent)
                                {
                                    inputs.push({
                                        txid: unspent.txid,
                                        n: unspent.index,
                                        script: unspent.script,
                                        value: unspent.value,
                                    });
                                    available_balance = available_balance + unspent.value;
                                });
                                
                                var amount_to_send = fee * 2;
                                if(available_balance > (10000 * fee))
                                {
                                    amount_to_send = (Math.floor(Math.random() * 100) + 10) * fee
                                }
                                else if(available_balance > (1000 * fee))
                                {
                                    amount_to_send = (Math.floor(Math.random() * 50) + 10) * fee
                                }
                                else if(available_balance > (100 * fee))
                                {
                                    amount_to_send = (Math.floor(Math.random() * 20) + 10) * fee
                                }
                                else if(available_balance > (50 * fee))
                                {
                                    amount_to_send = (Math.floor(Math.random() * 10) + 5) * fee
                                }
                                else if(available_balance > (20 * fee))
                                {
                                    amount_to_send = (Math.floor(Math.random() * 10) + 2) * fee
                                }
                                var outputs = [{
                                    'address': address,
                                    'value': amount_to_send
                                }];

                                var tx = $.fn.blockstrap.blockchains.raw(
                                    return_address, 
                                    false, 
                                    inputs, 
                                    outputs, 
                                    fee, 
                                    amount_to_send, 
                                    false,
                                    false
                                );
                                
                                $.ajax({
                                    url: 'php/claim.php',
                                    data: {email: email, chain: chain, code: code, address: address, tx: tx, sent: amount_to_send},
                                    dataType: 'json',
                                    method: 'post',
                                    success: function(results)
                                    {
                                        $(button).removeClass('loading');
                                        if($(hidden).css('display') == 'none')
                                        {
                                            $(hidden).show(350);
                                        }
                                        var success = false;
                                        var title = 'Warning';
                                        var content = 'Unknown error.';
                                        if(typeof results.success != 'undefined' && results.success === true)
                                        {
                                            title = 'Success';
                                            success = true;
                                        }
                                        if(typeof results.msg != 'undefined' && results.msg)
                                        {
                                            content = results.msg;
                                        }
                                        $.fn.blockstrap.core.modal(title, content);
                                    }
                                });
                            }
                        });
                    }
                    else
                    {
                        $.ajax({
                            url: 'php/claim.php',
                            data: {email: email, chain: chain, code: code, address: address},
                            dataType: 'json',
                            method: 'post',
                            success: function(results)
                            {
                                $(button).removeClass('loading');
                                if($(hidden).css('display') == 'none')
                                {
                                    $(hidden).show(350);
                                }
                                var success = false;
                                var title = 'Warning';
                                var content = 'Unknown error.';
                                if(typeof results.success != 'undefined' && results.success === true)
                                {
                                    title = 'Success';
                                    success = true;
                                }
                                if(typeof results.msg != 'undefined' && results.msg)
                                {
                                    content = results.msg;
                                }
                                if(typeof results.return_address != 'undefined' && results.return_address)
                                {
                                    $(form).find('input[name="return"]').val(results.return_address);
                                }
                                bs.core.modal(title, content);
                            }
                        });
                    }
                }
            }
            else
            {
                bs.core.modal('Warning', 'Email address required.');
            }
        });
    },
    forms: function()
    {
        bs_faucets.claim();
        bs_faucets.check();
    },
    init: function()
    {
        bs_faucets.forms();
    },
    qrs: function()
    {
        $('.qr-modal').on('click', function(e)
        {
            e.preventDefault();
            var button = this;
            var address = $(button).attr('data-address');
            var holder = $('#qr-modal').find('.modal-contents');
            if($(holder).find('img').length > 0)
            {
                $(holder).find('img').remove();
            }
            $(holder).qrcode({
                render: 'image',
                text: address
            });
            $(holder).parent().find('.qr-address').html('<strong>Address:</strong> '+address);
            $.fn.blockstrap.core.modal('QR Code', false, 'qr-modal');
        });
    }
};

$(document).ready(function()
{
    bs_faucets.init();
});

