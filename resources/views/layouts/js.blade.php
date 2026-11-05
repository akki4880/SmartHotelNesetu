  <script>
      /**
       *	This script should be placed right after the body tag for fast execution 
       *	Note: the script is written in pure javascript and does not depend on thirdparty library
       **/
      'use strict';

      var htmlRoot = document.getElementsByTagName('HTML')[0],
          classHolder = document.getElementsByTagName('BODY')[0],
          head = document.getElementsByTagName('HEAD')[0],
          themeID = document.getElementById('mytheme'),
          filterClass = function(t, e) {
              return String(t).split(/[^\w-]+/).filter(function(t) {
                  return e.test(t)
              }).join(' ')
          },
          /** 
           * Load theme options
           **/
          loadSettings = function() {
              var t = localStorage.getItem('themeSettings') || '',
                  e = t ? JSON.parse(t) : {};
              return Object.assign({
                  htmlRoot: '',
                  classHolder: '',
                  themeURL: ''
              }, e)
          },
          /** 
           * Save to localstorage 
           **/
          saveSettings = function() {
              themeSettings.htmlRoot = filterClass(htmlRoot.className, /^(root)-/i),
                  themeSettings.classHolder = filterClass(classHolder.className, /^(nav|header|footer|mod|display)-/i),
                  themeSettings.themeURL = themeID.getAttribute("href") ? themeID.getAttribute("href") : "",
                  localStorage.setItem("themeSettings", JSON.stringify(themeSettings))
          },
          /** 
           * Reset settings
           **/
          resetSettings = function() {
              localStorage.setItem("themeSettings", "")
          },
          themeSettings = loadSettings();

      themeID || ((themeID = document.createElement('link')).id = 'mytheme',
              themeID.rel = 'stylesheet',
              themeID.href = '',
              head.appendChild(themeID),
              themeID = document.getElementById('mytheme')),
          themeSettings.htmlRoot && (htmlRoot.className = themeSettings.htmlRoot),
          themeSettings.classHolder && (classHolder.className = themeSettings.classHolder),
          themeSettings.themeURL && themeID.setAttribute("href", themeSettings.themeURL);
  </script>
  <script src="{{asset('assets/js/vendors.bundle.js')}}"></script>
  <script src="{{asset('assets/js/app.bundle.js')}}"></script>
  <script src="{{asset('assets/js/datagrid/datatables/datatables.bundle.js')}}"></script>
  <script src="{{asset('assets/js/main-js.js')}}"></script>
  <script src="{{asset('assets/js/admin-dashboard/script.js')}}"></script>
  <script src="{{asset('assets/js/super-admin-dashboard/script.js')}}"></script>


  <script>
      function showButtonLoader(button) {
          if (!button) return; // 👈 avoids the error

          button.disabled = true;
          const originalText = button.innerHTML;
          button.setAttribute('data-original-text', originalText);
          button.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>`;
      }

      $(document).on('click', '.btn-loader', function() {
          showButtonLoader(this);
      });

      // Reset for ALL modals automatically
      $(document).on('hidden.bs.modal', '.modal', function() {

          // Reset inside modal
          $(this).find('.btn-loader').each(function() {
              resetButtonLoader(this);
          });

          // ALSO reset any stuck buttons globally (important)
          $('.btn-loader').each(function() {
              resetButtonLoader(this);
          });

      });

      function resetButtonLoader(button) {
          if (!button) return; // 👈 avoids the error

          const originalText = button.getAttribute('data-original-text');
          if (originalText) button.innerHTML = originalText;
          button.disabled = false;

          $(button).find('.waves-ripple').remove();
          $('.waves-ripple').remove();
      }
  </script>

  <!-- ✅ Toastr CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

  <!-- ✅ jQuery + Toastr JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
  <!-- select2 -->
  <script src="{{asset('assets/js/formplugins/select2/select2.bundle.js')}}"></script>

  <!-- summernote -->
  <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>

  <script>
      toastr.options = {
          "closeButton": true,
          "debug": false,
          "newestOnTop": true,
          "progressBar": true,
          "positionClass": "toast-top-right", // 👈 position: top-right
          "preventDuplicates": true,
          "onclick": null,
          "showDuration": "1000",
          "hideDuration": "2000",
          "timeOut": "6000", // was 3000 (now 6 sec)
          "extendedTimeOut": "3000",
          "showEasing": "swing",
          "hideEasing": "linear",
          "showMethod": "fadeIn",
          "hideMethod": "fadeOut"
      };

      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });

      $(document).ready(function() {
          $(function() {
              $('.select2').select2();
          });
      });

      $('#hotelSwitcher').on('change', function() {
          let adminId = $(this).val();

          if (!adminId) return;

          $.ajax({
              url: "{{ route('users.switchHotel') }}",
              type: "POST",
              data: {
                  _token: "{{ csrf_token() }}",
                  admin_id: adminId
              },
              success: function(res) {
                  toastr.success('Hotel selected successdully');
                  if (res.status) {
                      window.location.reload(); // refresh context
                  }
              }
          });
      });

      //   filter hide and show
      function toggleFilter() {
          let row = document.getElementById('filterRow');
          row.style.display = (row.style.display === 'none') ? 'table-row' : 'none';
      }

      //   for fetching the default_base_guests and default_max_extra_guests
      function loadRoomTypeGuestConfig(roomTypeId) {
          if (!roomTypeId) {
              $('[name="base_guest_count"]').val('');
              $('[name="max_extra_guests"]').val('');
              return;
          }

          $.ajax({
              url: "{{ url('room-management/room-types') }}/" + roomTypeId + "/guest-config",
              type: "GET",
              success: function(response) {
                  if (response.status) {
                      $('[name="base_guest_count"]').val(response.data.default_base_guests);
                      $('[name="max_extra_guests"]').val(response.data.default_max_extra_guests);
                  }
              },
              error: function() {
                  toastr.success('danger', 'Failed to load room type configuration.');
              }
          });
      }

      $('select[name="room_type_id"]').on('change', function() {
          let roomTypeId = $(this).val();
          loadRoomTypeGuestConfig(roomTypeId);
      });

      $('[name="base_guest_count"], [name="max_extra_guests"]').prop('readonly', true);

      $(document).on('click', '.toggle-password', function() {
          const $group = $(this).closest('.input-group');
          const $input = $group.find('input[type="password"], input[type="text"]');
          const $icon = $(this).find('i');

          if ($input.attr('type') === 'password') {
              $input.attr('type', 'text');
              $icon.removeClass('fa-eye').addClass('fa-eye-slash');
          } else {
              $input.attr('type', 'password');
              $icon.removeClass('fa-eye-slash').addClass('fa-eye');
          }
      });
  </script>

  <!-- as country is selected the state will show  -->
  <script>
      function loadStatesByCountry(countryElement, selectedState = null) {

          let countryId = $(countryElement).val();

          let target = $(countryElement).data('target');
          let stateDropdown = $(target);

          stateDropdown.html('<option value="">Loading...</option>');

          if (!countryId) {
              stateDropdown.html('<option value="">Select State</option>');
              return;
          }

          $.ajax({
              url: "{{ route('get.states.by.country') }}",
              type: "GET",
              data: {
                  country_id: countryId
              },
              success: function(states) {

                  let options = '<option value="">Select State</option>';

                  $.each(states, function(key, state) {
                      let selected = selectedState == state.id ? 'selected' : '';
                      options += `<option value="${state.id}" ${selected}>${state.state_name}</option>`;
                  });

                  stateDropdown.html(options).trigger('change');


              }
          });
      }


      $(document).ready(function() {

          $('.summernote').summernote({
              height: 180,
              toolbar: [
                  ['style', ['bold', 'italic', 'underline']],
                  ['para', ['ul', 'ol', 'paragraph']],
                  ['insert', ['link']],
                  ['view', ['codeview']]
              ]
          });
      });

      (function() {

          /**
           * Clear validation UI inside container
           */
          window.clearValidation = function(container) {
              container.find('.is-invalid').removeClass('is-invalid');
              container.find('[data-error]').text('');
              container.find('.error-icon').addClass('d-none');
          };


          /**
           * Apply Laravel 422 errors
           */
          window.applyValidationErrors = function(errors, container) {
              $.each(errors, function(key, value) {
                  let input = container.find('[name="' + key + '"]');

                  input.addClass('is-invalid');

                  input.closest('.input-group')
                      .find('.error-icon')
                      .removeClass('d-none');

                  container.find('[data-error="' + key + '"]').text(value[0]);
              });
          };


          /**
           * Auto clear when any modal closes
           */
          $(document).on('hidden.bs.modal', '.modal', function() {
              clearValidation($(this));
          });

      })();

      function handleValidationError(xhr, form) {

          if (xhr.status === 422) {

              let errors = xhr.responseJSON.errors;

              form.find('.is-invalid').removeClass('is-invalid');
              form.find('[data-error]').text('');
              form.find('.error-icon').addClass('d-none');

              let errorMessages = []; // ⭐ collect messages

              $.each(errors, function(key, value) {

                  let message = value[0];

                  // ================================
                  // ✅ Handle array fields (id_type.0)
                  // ================================
                  if (key.includes('.')) {

                      let parts = key.split('.');
                      let field = parts[0];
                      let index = parseInt(parts[1]);
                      let rowNumber = index + 1;

                      // Beautify field name
                      field = field.replace(/_/g, ' ');
                      field = field.charAt(0).toUpperCase() + field.slice(1);

                      message = field + " required in row " + rowNumber;

                      let input = form.find('[name="' + field.toLowerCase().replace(/ /g, '_') + '[]"]').eq(index);

                      input.addClass('is-invalid');
                      input.closest('.form-group').find('[data-error]').text(message);

                  } else {

                      let input = form.find('[name="' + key + '"]');
                      input.addClass('is-invalid');
                      form.find('[data-error="' + key + '"]').text(message);
                  }

                  errorMessages.push(message); // ⭐ save for toastr
              });

              // ================================
              // ✅ Show real messages in toastr
              // ================================
              toastr.error(errorMessages.join('<br>'));

              return true;
          }

          return false;
      }


      function handleFrontendValidation(form) {

          let isValid = true;

          // clear old errors
          form.find('.is-invalid').removeClass('is-invalid');
          form.find('[data-error]').text('');
          form.find('.error-icon').addClass('d-none');

          // check required fields
          form.find('[required]').each(function() {

              let input = $(this);
              let name = input.attr('name');

              if (!input.val() || input.val().trim() === '') {

                  input.addClass('is-invalid');
                  form.find('[data-error="' + name + '"]').text('This field is required.');

                  isValid = false;
              }
          });

          return isValid;
      }

      //global create and edit form submission jquery 
      $(document).on('submit', '.ajax-form', function(e) {
          e.preventDefault();

          let form = $(this);
          let button = form.find('[type="submit"]');

          if (!handleFrontendValidation(form)) {
              toastr.error('Please review the form and fix any errors.');
              return;
          }
          showButtonLoader(button[0]);

          let ajaxOptions = {
              url: form.attr('action'),
              type: form.attr('method') || 'POST',
              data: new FormData(form[0]), // ALWAYS
              contentType: false,
              processData: false,

              success: function(response) {

                  let modal = form.data('modal');

                  if (modal) {
                      $(modal).modal('hide');
                  }

                  if (form.data('reset') == 1) {
                      form[0].reset();
                      form.find('select.select2').val(null).trigger('change');
                  }
                  resetButtonLoader(button[0]);

                  toastr.success(response.message ?? 'Saved successfully');

                  //   if (form.data('reload') == 1) {
                  //   location.reload();
                  //   }

                  $('#table-container').load(location.href + ' #table-container > *');
              },

              error: function(xhr) {
                  resetButtonLoader(button[0]);

                  if (!handleValidationError(xhr, form)) {
                      toastr.error(
                          xhr.responseJSON?.message ??
                          'Something went wrong.'
                      );
                  }
              }
          };

          $.ajax(ajaxOptions);
      });

      //global clear button 
      $('#clearaddBtn').click(() => $('#CreateForm')[0].reset());

      //global toggle button 
      $(document).on('change', '.globalStatusToggle', function() {
          let checkbox = $(this);
          let url = "{{url('toggle-status')}}";
          let id = checkbox.data('id');
          let model = checkbox.data('model');

          $.ajax({
              url: url,
              type: 'POST',
              data: {
                  _token: $('meta[name="csrf-token"]').attr('content'),
                  model: model,
                  id: id,
              },
              success: function(res) {

                  toastr.success(res.message ?? 'Status updated successfully');

                  // let badge = checkbox.closest('tr').find('td:last span');

                  // if (res.status) {
                  //     badge.removeClass('badge-danger')
                  //         .addClass('badge-success')
                  //         .text('Active');
                  // } else {
                  //     badge.removeClass('badge-success')
                  //         .addClass('badge-danger')
                  //         .text('Inactive');
                  // }
                  $('#table-container').load(location.href + ' #table-container > *');

              },
              error: function() {
                  toastr.error('Failed to update status');

                  // rollback
                  checkbox.prop('checked', !checkbox.prop('checked'));
              }
          });
      });

      $(document).on('click', '.view-btn', function() {
          let url = $(this).data('url');

          $.get(url, function(res) {

              $('#viewModalBody').html(res);

              // Optional: extract title from response
              let title = $(res).filter('h5').first().text();
              if (title) {
                  $('#viewModalTitle').text(title);
              }

              $('#viewModal').modal('show');
          });
      });
  </script>