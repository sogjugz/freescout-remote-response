# FreeScout Remote Response

This repository contains a FreeScout module that allows processing responses via a remote server.

## Features

- Automatically processes conversations through a remote service and injects responses effortlessly.
- Allows different remote servers for each Mailbox, enabling personalized responses for each one.
- Integrates with almost any remote service. Supports `GET` and `POST` requests with customizable headers for authentication or token usage.
- Injects remote responses directly into the editor.

## Requirements

- FreeScout version 1.8 or higher

## Configuration and Usages

1. Upload the **SsRemoteResponse** module (ZIP) to your FreeScout **Modules** folder. Ensure the folder is named `SsRemoteResponse`.
2. For each mailbox you want enabled, configure the necessary fields to connect with your remote service.
3. If you want to use headers settings, ensure the headers are encoded in a valid JSON value. Example: `{"Authorization": "Bearer SomeTokenHere"}`
4. Toggle the **Enable** switch at the top of the mailbox settings page.
5. On the conversation page, use the **cloud** button in the toolbar to inject the remote response while replying.

## Important Notes

- This module only connects your FreeScout instance to a remote service. You are responsible for implementing the service, which must respond with the text to inject into the reply.

## Data Sent to the Remote Service

```json
{
    "conversation_content": "Customer: \n Initial request: \n Agent: First response \n Customer: Customer response",
    "customer_name": "John Doe",
    "customer_email": "John.doe@domain.com",
    "conversation_subject": "Test conversation"
}

The conversation content includes all conversation threads in a readable text format.

Your server must return a text response, which will be automatically injected into the reply editor.
```
