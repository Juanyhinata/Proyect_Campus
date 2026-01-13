// Next.js 13+ (app directory)
import { OpenAIStream, StreamingTextResponse } from 'ai';
import { Configuration, OpenAIApi } from 'openai-edge';

const config = new Configuration({
    apiKey: process.env.OPENAI_API_KEY, // ¡Pon tu clave aquí o en .env!
});
const openai = new OpenAIApi(config);

export async function POST(req) {
    const { messages } = await req.json();

    const response = await openai.createChatCompletion({
        model: 'gpt-4o',           // o 'gpt-3.5-turbo' si quieres más barato
        stream: true,
        messages,
        temperature: 0.7,
    });

    const stream = OpenAIStream(response);
    return new StreamingTextResponse(stream);
}