var bs_faucets = 
{
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
    forms: function()
    {
        
    },
    init: function()
    {
        bs_uploads.forms();
    }
};

$(document).ready(function()
{
    bs_faucets.init();
});

