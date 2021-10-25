<?php

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    exit('success');
}

?>
<p>This page does not load REDCap's headers, which means CSRF tokens will need to be manually added to forms and all javascript POST requests (including jQuery post()).</p>
<br>
<p>Click the following button to post an example form with CSRF protection:</p>
<input name='redcap_csrf_token' value='<?=$module->getCSRFToken()?>' type='hidden'>
<button data-include-csrf-token data-url=''>POST With CSRF Token</button>

<br>
<br>
<p>Click the following button to test a post to a pages listed under "no-csrf-pages" in config.json:</p>
<button data-url='<?=$module->getUrl('no-csrf-page.php')?>'>NO CSRF POST</button>

<script>
    document.querySelectorAll('button').forEach((button) => {
        let url = button.dataset.url
        if(url === ''){
            url = location.href
        }

        button.addEventListener('click', () => {
            const data = new URLSearchParams()

            if(button.dataset.includeCsrfToken !== undefined){
                data.append('redcap_csrf_token', <?=json_encode($module->getCSRFToken())?>)
            }

            fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                body: data
            })
            .then(response => response.text())
            .then(data => {
                if(data === 'success'){
                    alert('POST was successful!')
                }
                else{
                    alert('The POST failed with the following response: ' + data)
                }
            })
        })
    })
</script>