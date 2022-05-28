<script>
  $('table').on('click', 'a:not([href*=javascript],[class*=img])', function (e) {
    e.preventDefault();

    let href = $(this).attr('href');
    let isTargetBlank = ($(this).attr('target') == '_blank')

    if (href !== undefined && !href.includes('#')) {
      let token = getToken(href);

      if (token != false) {
        let separator = (this.href.indexOf('?') > -1) ? '&' : '?';

        if (isTargetBlank) {
          window.open(this.href + separator + 'token=' + token, '_blank')
        } else {
          window.location.href = this.href + separator + 'token=' + token;
        }
      } else {
        console.log('error token');
      }
    }
  })
  
  $('a:not([href*=javascript],[class*=img])').not('table a:not([href*=javascript],[class*=img])').on('click', function (e) {
    e.preventDefault();

    let href = $(this).attr('href');
    let isTargetBlank = ($(this).attr('target') == '_blank')

    if (href.includes('history.back()')) {
      history.back()
    }

    if (href !== undefined && !href.includes('#')) {
      let token = getToken(href);

      if (token != false) {
        let separator = (this.href.indexOf('?') > -1) ? '&' : '?';
        
        if (isTargetBlank) {
          window.open(this.href + separator + 'token=' + token, '_blank')
        } else {
          window.location.href = this.href + separator + 'token=' + token;
        }
      } else {
        console.log('error token');
      }
    }
  })
  
  // if (href.includes('edit') || href.includes('delete') || href.includes('pdf')) {
  //   let token = getToken(href);
  //   if (token != false) {
  //     window.location.href = this.href + '?token=' + token;
  //   } else {
  //     console.log('error token');
  //   }
  // } else {
  //   window.location.href = this.href;
  // }
  
  function getToken(url) {
    let response = false;
    $.ajax({
      method: "GET",
      url: "{{ route('superuser.getToken') }}",
      dataType: "json",
      async: false,
      data: {
        link: url
      },
      success: function(result) {
        response = result;
      }
    });
    return response;
  }
  </script>