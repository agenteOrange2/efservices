<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Owner-Operator Lease Agreement</title>
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
        <h1>OWNER-OPERATOR LEASE AGREEMENT</h1>
        <p class="note">
            <strong>Note:</strong>
            <span style="letter-spacing: 2px">
                This Owner-Operator Lease Agreement should be maintained by both parties
                during the term of the Agreement.
            </span>
        </p>

        <div class="section">
            <ol style="list-style-type: upper-roman; padding: 0">
                <li>
                    <div class="container-li">
                        <p style="line-height: 1.5; text-align: justify">
                            This AGREEMENT is made and entered into on <span class="underline" style="width: 120px">{{ $signedDate ?? now()->format('m/d/Y') }}</span>,
                            between <span class="underline" style="width: 40%">{{ $carrierName ?? '' }}</span> ("CARRIER"),
                            with its principal place of business at <span class="underline" style="width: 40%">{{ $carrierAddress ?? '' }}</span>,
                            and <span class="underline" style="width: 40%">{{ $ownerName ?? '' }}</span> ("OWNER-OPERATOR").
                        </p>
                    </div>
                </li>
                <li>
                    <div class="container-li">
                        <p style="line-height: 1.5; text-align: justify">
                            <strong>PURPOSE OF AGREEMENT:</strong> The OWNER-OPERATOR owns the equipment described herein
                            and desires to lease said equipment with driver services to the CARRIER. The CARRIER is engaged
                            in the business of transporting freight by motor vehicle and desires to lease equipment with
                            driver services from the OWNER-OPERATOR. This Agreement sets forth the terms and conditions
                            under which the OWNER-OPERATOR will provide equipment and services to the CARRIER.
                        </p>
                    </div>
                </li>
                <li>
                    <div class="container-li">
                        <p><strong>OWNER-OPERATOR AND EQUIPMENT INFORMATION:</strong></p>
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
                        <p><strong>EXCLUSIVE POSSESSION AND CONTROL:</strong></p>
                        <p style="line-height: 1.5; text-align: justify">
                            The CARRIER shall have exclusive possession, control, and use of the equipment
                            for the duration of this Agreement as required by 49 CFR § 376.12(c)(1). The CARRIER
                            assumes complete responsibility for the operation of the equipment during the lease term
                            for regulatory compliance purposes. However, this provision does not affect the relationship
                            between the CARRIER and the OWNER-OPERATOR, which is that of an independent contractor and not
                            an employer-employee relationship.
                        </p>
                    </div>
                </li>
                <li>
                    <div class="container-li">
                        <p><strong>TERM AND TERMINATION:</strong></p>
                        <p style="line-height: 1.5; text-align: justify">
                            This Agreement shall commence on the date first written above and shall continue until terminated
                            by either party upon thirty (30) days written notice. Either party may terminate this Agreement
                            immediately for breach by the other party. Upon termination, all obligations that are still
                            executory on both sides are discharged, but any right based on prior breach or performance survives.
                        </p>
                    </div>
                </li>
                <li>
                    <div class="container-li">
                        <p><strong>COMPENSATION:</strong></p>
                        <p style="line-height: 1.5; text-align: justify">
                            The OWNER-OPERATOR shall be compensated as follows:<br />
                            a) Settlement statements will be provided at least every 7 days detailing:<br />
                            &nbsp;&nbsp;&nbsp;• Trip earnings<br />
                            &nbsp;&nbsp;&nbsp;• Deductions (fuel advances, escrow, insurance, etc.)<br />
                            &nbsp;&nbsp;&nbsp;• Net settlement amount<br />
                            b) Payment will be made no later than 15 days after submission of necessary delivery
                            documents and paperwork.<br />
                            c) All chargebacks or deductions to the OWNER-OPERATOR's compensation must be specified
                            in writing and agreed upon in advance.<br />
                            d) The CARRIER will provide a written explanation of any deduction at the time of settlement.
                        </p>
                    </div>
                </li>
                <li>
                    <div class="container-li">
                        <p><strong>INSURANCE:</strong></p>
                        <p style="line-height: 1.5; text-align: justify">
                            a) The CARRIER shall provide and maintain public liability and property damage insurance
                            as required under 49 CFR Part 387.<br />
                            b) The OWNER-OPERATOR shall be responsible for:<br />
                            &nbsp;&nbsp;&nbsp;• Non-trucking liability (bobtail) insurance<br />
                            &nbsp;&nbsp;&nbsp;• Physical damage insurance on the equipment<br />
                            &nbsp;&nbsp;&nbsp;• Occupational accident or workers' compensation coverage (if required)<br />
                            c) If the OWNER-OPERATOR chooses to provide their own primary liability insurance,
                            they must provide a Certificate of Insurance naming the CARRIER as an Additional Insured.
                        </p>
                    </div>
                </li>
                <li>
                    <div class="container-li">
                        <p><strong>EQUIPMENT MAINTENANCE:</strong></p>
                        <p style="line-height: 1.5; text-align: justify">
                            The OWNER-OPERATOR is responsible for maintaining the equipment in safe operating condition
                            and in compliance with all federal and state laws and regulations. The CARRIER may require
                            proof of inspections and maintenance. The OWNER-OPERATOR agrees to comply with all safety
                            regulations and to maintain all applicable operating permits and authorities.
                        </p>
                    </div>
                </li>
                <li>
                    <div class="container-li">
                        <p><strong>INDEPENDENT CONTRACTOR STATUS:</strong></p>
                        <p style="line-height: 1.5; text-align: justify">
                            The relationship between the CARRIER and OWNER-OPERATOR is that of an independent contractor
                            and not an employer-employee relationship. The OWNER-OPERATOR has the right to control the
                            manner and means of performing services under this Agreement, subject to the CARRIER's right
                            to direct the results to be accomplished. The OWNER-OPERATOR is responsible for all applicable
                            taxes and business expenses related to their operation.
                        </p>
                    </div>
                </li>
            </ol>
        </div>

        <div class="signature-section">
            <table style="width: 100%">
                <tr>
                    <td class="signature-col">
                        <strong>MOTOR CARRIER</strong><br /><br />
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
                        <strong>OWNER-OPERATOR</strong><br /><br />
                        By: <span class="underline" style="width: 80%">{{ $ownerName ?? '' }}</span
                        ><br /><br />
                        Date: <span class="underline" style="width: 80%">{{ $signedDate ?? now()->format('m/d/Y') }}</span>
                        <br /><br />
                        <p class="signature-container">
                            @if (!empty($signaturePath) && file_exists($signaturePath))
                                <img src="{{ $signaturePath }}" alt="Firma Digital" style="max-width: 100%; max-height: 100px;">
                            @elseif (!empty($signature))
                                <img src="{{ $signature }}" alt="Firma Digital" style="max-width: 100%; max-height: 100px;">
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
            <p>&copy; {{ date('Y') }} Printed by EF Services LLC - {{ now()->format('F d, Y') }}</p>
        </div>
    </body>
</html>
