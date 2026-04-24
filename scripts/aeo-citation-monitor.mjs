import fs from 'fs';
import path from 'path';

// Usage: node scripts/aeo-citation-monitor.mjs
// Requirements: Node 18+ (uses native fetch)
// Environment variables needed: OPENAI_API_KEY, PERPLEXITY_API_KEY

const OPENAI_API_KEY = process.env.OPENAI_API_KEY;
const PERPLEXITY_API_KEY = process.env.PERPLEXITY_API_KEY;

const LOG_DIR = path.join(process.cwd(), 'data', 'aeo-logs');
const LOG_FILE = path.join(LOG_DIR, 'citations.json');

const QUERIES = [
  "What is the Vintage Modified Racing Association?",
  "Where does VMRA race in 2026?",
  "What are the rules for vintage modified circle track cars in the PNW?"
];

async function queryOpenAI(prompt) {
  if (!OPENAI_API_KEY) return { error: "OPENAI_API_KEY not set" };
  try {
    const res = await fetch('https://api.openai.com/v1/chat/completions', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${OPENAI_API_KEY}`
      },
      body: JSON.stringify({
        model: 'gpt-4o-mini',
        messages: [{ role: 'user', content: prompt }]
      })
    });
    const data = await res.json();
    return data.choices?.[0]?.message?.content || "No response";
  } catch (err) {
    return { error: err.message };
  }
}

async function queryPerplexity(prompt) {
  if (!PERPLEXITY_API_KEY) return { error: "PERPLEXITY_API_KEY not set" };
  try {
    const res = await fetch('https://api.perplexity.ai/chat/completions', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${PERPLEXITY_API_KEY}`
      },
      body: JSON.stringify({
        model: 'llama-3.1-sonar-small-128k-online',
        messages: [{ role: 'user', content: prompt }]
      })
    });
    const data = await res.json();
    return data.choices?.[0]?.message?.content || "No response";
  } catch (err) {
    return { error: err.message };
  }
}

async function runMonitor() {
  console.log("Starting AEO Citation Monitor for VMRA...");
  
  if (!fs.existsSync(LOG_DIR)) {
    fs.mkdirSync(LOG_DIR, { recursive: true });
  }

  const results = {
    timestamp: new Date().toISOString(),
    platforms: {
      chatgpt: {},
      perplexity: {}
    }
  };

  for (const q of QUERIES) {
    console.log(`Querying ChatGPT for: "${q}"`);
    results.platforms.chatgpt[q] = await queryOpenAI(q);
    
    console.log(`Querying Perplexity for: "${q}"`);
    results.platforms.perplexity[q] = await queryPerplexity(q);
  }

  // Load existing log
  let history = [];
  if (fs.existsSync(LOG_FILE)) {
    try {
      history = JSON.parse(fs.readFileSync(LOG_FILE, 'utf8'));
    } catch (e) {
      console.warn("Could not parse existing log, starting fresh.");
    }
  }

  history.push(results);
  fs.writeFileSync(LOG_FILE, JSON.stringify(history, null, 2));

  console.log(`Completed. Logged to ${LOG_FILE}`);
}

runMonitor().catch(console.error);
