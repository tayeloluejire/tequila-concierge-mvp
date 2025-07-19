// netlify/functions/getVisaInfo.js

const axios = require('axios'); // Placeholder for future API calls
require('dotenv').config();

exports.handler = async function(event, context) {
    if (event.httpMethod !== 'POST') {
        return {
            statusCode: 405,
            body: JSON.stringify({ success: false, message: 'Method Not Allowed. Use POST.' }),
        };
    }

    let origin, destination, purpose;
    try {
        const body = JSON.parse(event.body);
        origin = body.origin;
        destination = body.destination;
        purpose = body.purpose;
    } catch (err) {
        return {
            statusCode: 400,
            body: JSON.stringify({ success: false, message: 'Invalid JSON input.' }),
        };
    }

    if (!origin || !destination || !purpose) {
        return {
            statusCode: 400,
            body: JSON.stringify({
                success: false,
                message: 'Missing required parameters: origin, destination, purpose',
            }),
        };
    }

    // Normalize input
    const originLower = origin.toLowerCase();
    const destinationLower = destination.toLowerCase();

    let visaData;

    if (originLower.includes("nigeria") && destinationLower.includes("canada")) {
        visaData = {
            success: true,
            country: "Canada",
            requirements: [
                "Valid passport (6+ months)",
                "Completed IMM 5257 form",
                "Family Information form (IMM 5707)",
                "Two passport-sized photos",
                "Proof of funds (bank statement, pay slips)",
                "Letter of invitation (if applicable)",
                "Travel itinerary (flights, hotel)",
                "Biometrics at VAC",
                "Visa fee receipt"
            ],
            processingTime: "4–8 weeks (can vary)",
            cost: "CAD 100 (visa), CAD 85 (biometrics)",
            notes: "Apply online. Some applicants may require interviews.",
            link: "https://www.canada.ca/en/immigration-refugees-citizenship/services/visit-canada/apply-visitor-visa.html"
        };
    } else if (originLower.includes("nigeria") && destinationLower.includes("usa")) {
        visaData = {
            success: true,
            country: "United States",
            requirements: [
                "Valid passport",
                "DS-160 confirmation",
                "Visa appointment confirmation",
                "One 2x2 inch photo",
                "Evidence of funds",
                "Ties to Nigeria (job, family, property, etc.)"
            ],
            processingTime: "Interview + days to weeks after",
            cost: "USD 185 (B1/B2 visitor visa)",
            notes: "Apply early. Interview appointments can be delayed.",
            link: "https://ng.usembassy.gov/visas/"
        };
    } else {
        visaData = {
            success: false,
            message: `Sorry, I don’t yet have visa info for travel from ${origin} to ${destination} for ${purpose}.`,
            suggestion: "Please check the official embassy website or contact a licensed visa consultant.",
            link: `https://www.google.com/search?q=${encodeURIComponent(destination)}+visa+requirements+from+${encodeURIComponent(origin)}`
        };
    }

    return {
        statusCode: 200,
        body: JSON.stringify(visaData),
    };
};
