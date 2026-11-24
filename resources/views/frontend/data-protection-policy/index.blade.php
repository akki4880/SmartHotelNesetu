@extends('frontend.layouts.index')

@section('title', 'Data Protection & Security Policy | SmartHotel')

@section('content')
<section class="section-pad bg-sh-light">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9">
        <article class="bg-white rounded-4 shadow-sm p-4 p-md-5">
          <div class="mb-4">
            <div class="sh-section-tag"><i class="bi bi-shield-lock"></i> Legal</div>
            <h1 class="sh-section-title mb-2">SMART HOTEL – DATA PROTECTION &amp; SECURITY POLICY</h1>
            <p class="text-muted mb-1"><strong>Operated by Netsetu</strong></p>
            <p class="text-muted"><strong>Effective Date:</strong> 24 November 2026</p>
          </div>

          <section class="mb-4"><h2 class="h4">1. Purpose</h2>
            <p>This policy explains the data protection and security practices applicable to SmartHotel, including guest identity documents, booking records, employee information and financial records.</p>
          </section>

          <section class="mb-4"><h2 class="h4">2. Data Categories</h2>
            <p>SmartHotel may process:</p>
            <ol><li>Guest personal information.</li><li>Guest identification documents.</li><li>Hotel and business information.</li><li>Employee attendance and payroll records.</li><li>Financial and invoice records.</li><li>Authentication and technical logs.</li></ol>
          </section>

          <section class="mb-4"><h2 class="h4">3. Roles and Responsibilities</h2>
            <p>The hotel generally determines the purposes for which guest and employee information is collected and used.</p>
            <p>Netsetu provides the software infrastructure and processes customer information to deliver the contracted services.</p>
            <p>The precise legal roles of each party will depend on the relevant processing activity and applicable law.</p>
          </section>

          <section class="mb-4"><h2 class="h4">4. Access Control</h2>
            <p>SmartHotel should use role-based access controls.</p>
            <p>Recommended roles include:</p>
            <ul><li>Hotel Owner.</li><li>Hotel Administrator.</li><li>Receptionist.</li><li>Manager.</li><li>Accountant.</li><li>HR or Payroll Administrator.</li></ul>
            <p>Access to guest identity documents and payroll information should be limited to personnel who need it for their assigned duties.</p>
            <p>Hotels must promptly deactivate access for employees who leave or no longer require access.</p>
          </section>

          <section class="mb-4"><h2 class="h4">5. Identity Document Protection</h2>
            <p>Identity documents uploaded through SmartHotel must be protected using appropriate safeguards.</p>
            <p>Recommended technical controls include:</p>
            <ul><li>Private cloud storage.</li><li>Encryption in transit.</li><li>Encryption at rest.</li><li>Restricted file access.</li><li>Time-limited download links.</li><li>Secure authentication.</li><li>Audit logs for viewing, downloading and modifying documents.</li><li>Regular access reviews.</li><li>Secure deletion procedures.</li></ul>
            <p>Identity document files must not be placed in publicly accessible directories.</p>
            <p>Hotels must not use uploaded identity documents for purposes unrelated to legitimate hotel operations.</p>
          </section>

          <section class="mb-4"><h2 class="h4">6. Data Encryption</h2>
            <p>Netsetu will implement or maintain appropriate encryption measures according to the deployed infrastructure and security design.</p>
            <p>The actual encryption algorithms, key management procedures and backup arrangements should be documented internally.</p>
          </section>

          <section class="mb-4"><h2 class="h4">7. Data Backup and Recovery</h2>
            <p>SmartHotel may use backup systems to support data recovery and business continuity.</p>
            <p>Backup retention periods, restoration procedures and recovery objectives will depend on the selected infrastructure and service plan.</p>
            <p>Backups must be protected against unauthorized access.</p>
          </section>

          <section class="mb-4"><h2 class="h4">8. Security Monitoring</h2>
            <p>Security monitoring may include:</p>
            <ul><li>Login activity.</li><li>Failed login attempts.</li><li>Administrative actions.</li><li>Document access events.</li><li>Unusual system activity.</li><li>Server and application errors.</li></ul>
            <p>These logs may be used for investigation, security monitoring and troubleshooting.</p>
          </section>

          <section class="mb-4"><h2 class="h4">9. Data Breach Management</h2>
            <p>Netsetu will maintain procedures for identifying, investigating and responding to suspected data breaches.</p>
            <p>Where a breach affects hotel data, Netsetu will notify the affected hotel without undue delay under the applicable contract and assist with investigation and response.</p>
            <p>The parties will cooperate regarding legally required notifications to affected individuals and authorities.</p>
          </section>

          <section class="mb-4"><h2 class="h4">10. Third-Party Cloud Providers</h2>
            <p>Netsetu may engage authorized cloud hosting, storage, backup and infrastructure providers.</p>
            <p>Such providers may process information only for the services they provide and subject to applicable contractual and legal safeguards.</p>
          </section>

          <section class="mb-4"><h2 class="h4">11. Data Retention and Deletion</h2>
            <p>Personal information must not be retained indefinitely without a valid purpose.</p>
            <p>Hotels are responsible for identifying applicable statutory retention periods for guest, financial and employee records.</p>
            <p>Upon account termination, information will be handled according to the applicable Data Processing Agreement and legal requirements.</p>
          </section>

          <section class="mb-4"><h2 class="h4">12. Employee Confidentiality</h2>
            <p>Netsetu personnel and authorized service providers who may access customer information must be subject to appropriate confidentiality and access restrictions.</p>
          </section>

          <section class="mb-4"><h2 class="h4">13. Security Limitations</h2>
            <p>Although appropriate security measures will be maintained, no digital system can guarantee absolute security.</p>
            <p>Customers must also protect their own devices, passwords, networks and employee access.</p>
          </section>

          <section class="mb-4"><h2 class="h4">14. Reporting Security Issues</h2>
            <p>Report suspected security incidents or unauthorized access to:</p>
            <p class="mb-1"><strong>Security Email:</strong> [SECURITY EMAIL]</p>
            <p class="mb-1"><strong>Company:</strong> Netsetu</p>
            <p>Please include relevant details without sending unnecessary copies of identity documents or other sensitive information.</p>
          </section>

          <section><h2 class="h4">15. Policy Updates</h2>
            <p>Netsetu may update this policy to reflect changes in security technology, infrastructure and legal requirements.</p>
          </section>
        </article>
      </div>
    </div>
  </div>
</section> 
@endsection