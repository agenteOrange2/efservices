<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Lease Agreement</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 40px;
                line-height: 1.5;                
            }
            p{
                font-size: 14px;
                margin: 7px 0
            }
            h1 {
                text-align: center;
                font-size: 24px;
                font-weight: bold;
            }
            .note {
                text-align: center;
                font-style: italic;
            }
            .section {
                margin-top: 30px;
            }
            .section-title {
                font-weight: bold;
            }
            .underline {
                display: inline-block;
                border-bottom: 1px solid #000;
                min-width: 100px;
            }
            .container-li {
                margin: 0px 0px 0px 30px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            td {
                vertical-align: top;
                padding: 4px 8px;
            }
            .signature-section {
                margin-top: 50px;
            }
            .signature-col {
                width: 45%;
            }

            .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
        </style>
    </head>
    <body>
        <h1>LEASE AGREEMENT</h1>
        <p class="note">
            <strong>Note:</strong>
            <span style="letter-spacing: 2px">
                This lease Agreement should be maintained in the Equipment
                during the term of the Agreement.
            </span>
        </p>

        <div class="section">
            <ol style="list-style-type: upper-roman; padding: 0">
                <li>
                    <div class="container-li">
                        I,
                        <span class="underline" style="width: 70%">{{ $carrierName ?? '' }}</span>
                        (Carrier/Registrant) <br />
                        Address:
                        <span class="underline" style="width: 74%">{{ $carrierAddress ?? '' }}</span>,
                        and<br />
                        <span class="underline" style="width: 50%">{{ $ownerName ?? '' }}</span>
                        (Equipment Owner)
                    </div>
                    <div class="container-li">
                        <p style="line-height: 1.5; text-align: justify">
                            are parties to a written Lease Agreement (Agreement)
                            whereby the Equipment Owner has leased to the
                            Carrier certain motor vehicle equipment listed
                            below, owned and controlled by the Equipment Owner
                            whereby the Equipment Owner is providing the Carrier
                            as operator or operators of the Equipment for the
                            purpose of loading, transporting and unloading
                            freight.
                        </p>
                    </div>
                </li>
                <li>
                    <div class="container-li">
                        <p style="line-height: 1.5.5; text-align: justify">
                            The Carrier shall have
                            <strong
                                >exclusive possession, control, and use</strong
                            >
                            of the equipment for the duration of this Agreement
                            as required by 49 CFR § 376.12(c)(1). The Carrier
                            assumes complete responsibility for the operation of
                            the equipment during the lease term.
                        </p>
                    </div>
                </li>
                <li>
                    <div class="container-li">
                        <p>Equipment Owner/Equipment Information</p>
                        <table>
                            <tr>
                                <td>
                                    Name:
                                    <span
                                        class="underline"
                                        style="width: 90%"
                                    >{{ $ownerName ?? '' }}</span>
                                </td>
                                <td>
                                    Phone #:
                                    <span
                                        class="underline"
                                        style="width: 90%"
                                    >{{ $ownerPhone ?? '' }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    dba:
                                    <span
                                        class="underline"
                                        style="width: 90%"
                                    >{{ $ownerDba ?? '' }}</span>
                                </td>
                                <td>
                                    Contact:
                                    <span
                                        class="underline"
                                        style="width: 90%"
                                    >{{ $ownerContact ?? '' }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Address:
                                    <span
                                        class="underline"
                                        style="width: 90%"
                                    >{{ $ownerAddress ?? '' }}</span>
                                </td>
                                <td>
                                    FEIN:
                                    <span
                                        class="underline"
                                        style="width: 90%"
                                    >{{ $ownerFein ?? '' }}</span>
                                </td>
                            </tr>
                        </table>

                        <table style="margin-top: 20px">
                            <tr>
                                <td>
                                    Year:
                                    <span
                                        class="underline"
                                        style="width: 80px"
                                    >{{ $vehicleYear ?? '' }}</span>                                    
                                </td>
                                <td>
                                    Make:
                                    <span
                                        class="underline"
                                        style="width: 80px"
                                    >{{ $vehicleMake ?? '' }}</span>
                                </td>
                                <td>
                                    VIN:
                                    <span
                                        class="underline"
                                        style="width: 180px"
                                    >{{ $vehicleVin ?? '' }}</span>                                    
                                </td>
                                <td>
                                    Unit #:
                                    <span
                                        class="underline"
                                        style="width: 80px"
                                    >{{ $vehicleUnit ?? '' }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </li>
                <li>
                    <div class="container-li">
                        <p>Duration of Lease Agreement and Termination</p>
                        <p>
                            The Lease Agreement shall begin on the date below
                            and shall remain in effect until terminated by
                            either party giving notice to that effect. Notice
                            may be given personally by mail or by fax at the
                            address or fax number shown in the Lease Agreement.
                        </p>
                    </div>
                </li>
                <li>
                    <div class="container-li">
                        <p>
                            The Owner shall be compensated as follows:<br />
                            Settlement statements will be provided at least
                            every 7 days detailing:<br /> 
                            Trip earnings, deductions
                            (fuel advances, escrow, insurance, etc.)<br/> 
                            Net settlement amount. <br/>
                            Payment will be made no later than days after submission of necessary delivery
                            documents and paperwork.
                        </p>
                    </div>
                </li>
                <li>
                    <div class="container-li">
                        <p>
                            All chargebacks or deductions to the Owner's
                            compensation must be specified in writing and agreed
                            upon in advance.<br />
                            The Carrier will provide a written explanation of
                            any deduction at the time of settlement.
                        </p>
                    </div>
                </li>
                <li>
                    <div class="container-li">
                        <p>
                            The Carrier shall provide public liability and
                            property damage insurance as required under 49 CFR
                            Part 387 or if the Owner decided to run with own
                            insurance an Additional Insured (Certificate Holder)
                            need to be provided to Carrier with Carrier
                            information on it. Also, the Owner is responsible
                            for:<br />
                            Non-trucking liability (bobtail) insurance<br />
                            Occupational accident or workers' compensation
                            coverage (if required).
                        </p>
                    </div>
                </li>
                <li>
                    <div class="container-li">
                        <p>
                            The Owner is responsible for maintaining the
                            equipment in safe operating condition and in
                            compliance with all federal and state laws. The
                            Carrier may require proof of inspections and
                            maintenance.
                        </p>
                    </div>
                </li>
            </ol>
        </div>

        <div class="signature-section">
            <table style="width: 100%">
                <tr>
                    <td class="signature-col">
                        <strong>MOTOR CARRIER/REGISTRANT</strong><br /><br />
                        By: <span class="underline" style="width: 80%">{{ $carrierName ?? '' }}</span
                        ><br /><br />
                        Date: <span class="underline" style="width: 80%">{{ $signedDate ?? now()->format('m/d/Y') }}</span
                        ><br /><br />
                        MC #: <span class="underline" style="width: 80%">{{ $carrierMc ?? '' }}</span
                        ><br /><br />
                        USDOT #:
                        <span class="underline" style="width: 80%">{{ $carrierUsdot ?? '' }}</span>
                    </td>
                    <td class="signature-col">
                        <strong>EQUIPMENT OWNER</strong><br /><br />
                        By: <span class="underline" style="width: 80%">{{ $ownerName ?? '' }}</span
                        ><br /><br />
                        Date: <span class="underline" style="width: 80%">{{ $signedDate ?? now()->format('m/d/Y') }}</span>
                        <br /><br />
                        <p class="signature-container">
                            {{-- Primero intentar usar la ruta física de la firma (como en certification.blade.php) --}}
                            @if (!empty($signaturePath) && file_exists($signaturePath))
                                <img src="{{ $signaturePath }}" alt="Firma Digital" style="max-width: 100%; max-height: 100px;">
                                {{-- Si no hay ruta física, intentar usar los datos base64 --}}
                            @elseif(isset($signatureData) && !empty($signatureData))
                                @php
                                    // Depurar la información de la firma
                                    $signatureType = 'desconocido';
                                    $signatureLength = strlen($signatureData);
                                    $signatureStart = substr($signatureData, 0, 30);
                
                                    if (strpos($signatureData, 'data:image') === 0) {
                                        $signatureType = 'base64';
                                        // Asegurarse de que la firma base64 esté limpia
                                        $cleanSignature = $signatureData;
                                    } elseif (filter_var($signatureData, FILTER_VALIDATE_URL)) {
                                        $signatureType = 'url';
                                        $cleanSignature = $signatureData;
                                    } else {
                                        $signatureType = 'raw';
                                        // Convertir a base64 si no es base64 ni URL
                                        $cleanSignature = 'data:image/png;base64,' . base64_encode($signatureData);
                                    }
                                @endphp
                
                                {{-- Mostrar la firma según su tipo --}}
                                <img src="{!! $cleanSignature !!}" alt="Firma Digital" style="max-width: 100%; max-height: 100px;">
                            @else
                                <div
                                    style="border: 1px dashed #ccc; height: 100px; display: flex; align-items: center; justify-content: center;">
                                    <p style="color: #999;">Firma no disponible</p>
                                </div>
                            @endif
                        </p>

                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">            
            <p>&copy; {{ date('Y') }} Printed by EF Services LLC April 29, 2025</p>
        </div>
    </body>
</html>
