A Redis Stream is an append-only log where you add messages, and consumers can read them at their own pace.

Example stream name: "mystream"

When you add messages, each gets a unique ID.

- [Basic Usage]
    - `XADD mystream * user_id 123 action "login"` add a message
        - xadd .. add a message
        - mystream .. stream name
        - * auto generate unique id (instead you can put your unique id manually but rarely  used)
        - each message is key value pair message = {"user_id":123,"action":"login"}
        - this command will return output of unique id of message = 1696512367893-0

    - `XREAD BLOCK 0 STREAMS mystream $` block untill message arrive, once message arrive it returns
        - xread .. read message
        - block 0 .. block forever ... (block 5000 block for 5 seconds)
        - STREAMS mystream .. listen on this stream (can be multiple streams as well)
        - $ after now (it can be 1696512323123-0 then its after this id)

    - `XREAD STREAMS mystream 1696512360000-0` .. read messages >= 1696512360000-0 with no blocking, so read them now

    - `XRANGE mystream - +` read all messages from start to finish
        - read from stream in range XRANGE <stream> <start-id> <end-id>
        - (-) the earlies possible id
        - (+) the latest possible id
        - its dangerouse if stream has many messages

    - `XRANGE mystream - + COUNT 100` get only 100 then we are safe

    - `XREVRANGE mystream + - COUNT 100` .. latest 100 message .. read messages in reverse order from newest to oldest 

    - `XRANGE mystream 1696512355000-0 +` .. from id till latest
    - `XRANGE mystream 1696512355000-0 1696512360000-0` .. from id to id
    - `XRANGE mystream 1696512423123-0 1696512423123-0` .. read a specific message id

- [Message Queue]
    - Idea:
        - Stream: a log of messages you write to 
        - Consumer Group: A named group that coordinates how multiple workers (consumers) read from the same stream without duplicating effort.
        - Worker: A consumer that reads messages from the group.

    - Init:
        - `XGROUP CREATE mystream mygroup 0 MKSTREAM` .. create group
            - create mygroup around mystream 
            - group will work on messages since beginning 0
            - MKSTREAM is a flag that to create stream if not created already

        - `XREADGROUP GROUP mygroup worker-1 COUNT 1 BLOCK 0 STREAMS mystream >` .. start worker-1
            - start worker-1 on group mygroup
            - count 1: get 1 message per time
            - block 0: block forever till you receive message
            - STREAMS mysteam: listening on this stream
            - ">" : Only read new messages not yet delivered to this group
            - it returns immediately after receiving a message
            - message becomes pending after it is delivered to one of the workers, message is assigned to it and becomes pending

        - `XREADGROUP GROUP mygroup worker-2 COUNT 1 BLOCK 0 STREAMS mystream >` .. start worker-2

        - `XACK mystream mygroup <message-id>` .. Acknowledge when worker finish process message

        - `XCLAIM mystream mygroup worker-2 60000 1696520000000-0`
            - When a worker reads a message using XREADGROUP. Redis marks that message as pending for that consumer until it's ACKed. Now the message is assigned to worker-1 and will NOT be delivered to others.
            - That message is stuck in the PENDING list and won’t be processed again unless you reclaim it.
            - it wont even be retried on same consumer, it will just sit there on pending list till someone claim it
            - worker-2 is the new consumer 
            - 60000 only claim if idle more than 60 sec
            - 1696520000000-0 message id you want to claim

        - `XPENDING mystream mygroup` .. give summary of pending messages
        - `XPENDING mystream mygroup - + 10` .. get 10 pending messages

Q: i can open many listeners to stream using worker-1? then i can put like 10 workers and name them all worker 1?

You can technically start multiple listeners using the same consumer name. but that’s a BAD idea in Redis Streams
- Redis sees them as one single consumer, not 10.
- Messages are not load-balanced
- XPENDING will show: "worker-1" → 50 pending .. But you won’t know which physical worker actually has which message.
- If two processes with the same name worker-1 try to ACK or CLAIM messages, they step on each other’s state.
    Example disaster:

    Message assigned to worker-1
    Process A reads it
    Process B (same name) tries to ACK it
    If ACK happens twice → error or data inconsistencies

Q: can i see the messages that not assigned to workers yet?
Redis does not provide a direct command like: “Give me only undelivered messages”
you have to run a script to extract them

Q: what should i do if worker failed to process message? should worker still acknowledge it?
YES, in most real-world systems you should still XACK failed messages.. If you don’t ACK them, they will remain stuck in the pending list forever and never be reprocessed unless you explicitly reclaim or delete them.

Best practice is:
- 1️⃣ Worker reads message
- 2️⃣ Attempts to process
- 3️⃣ If it fails:
    - Log the failure
    - Store the message somewhere else (dead-letter queue/stream)
    - Then XACK so it no longer remains pending

Q: now if stream keep getting messages, will it build up forever, or there is a way to shrink it later?

By default, a Redis Stream will grow forever — every new XADD just keeps adding entries, and Redis won’t delete anything automatically.

- trim on writting (most common)
    - `XADD mystream MAXLEN ~ 10000 * field "value"` .. Keeps around 10k recent messages .. Deletes older ones in the background

- trim later
    - `XTRIM mystream MAXLEN ~ 10000`


Q: on real world scenarios, redis stream is difficult to debug?

you are correct

- Messages delivered to a consumer become PENDING, so they don’t show up with XREADGROUP > again. If a worker crashes or hangs and doesn’t XACK, messages just sit there silently.
- Redis doesn't provide one command to see: Delivered & ACKed messages, Pending (unprocessed) messages, Never-read messages
You need to combine: XPENDING → only pending .. XRANGE → all messages
- Duplicate consumer names = invisible chaos .. If team members reuse names (worker-1 everywhere), Redis can't track responsibility clearly.
- No built-in logs or history of what happened .. Redis Streams don’t keep:
    - When a message was delivered
    - Whether a worker crashed
    - Who tried to ACK and failed
    - Retries count history
    - Only idle time + delivery attempts.
- Dead letters are not automatic: If a message is never ACKed, Redis just keeps it in the PEL. It never moves it to a dead-letter queue by itself.



Q: How Real-World Teams Make Streams Debug-Friendly (best they can)?
- Unique consumer names (worker-1, worker-2, etc...)
- Use XACK religiously: Every message should be ACKed after handling, even failed ones (after logging or moving them).
- Add monitoring around pending messages: `XPENDING mystream mygroup - + 100` Alert if too many pending messages pile up.
- Reassign stuck jobs: Use XCLAIM for idle messages (you will need to write retry script to auto retry)
- Use a dead-letter design: If a message exceeds N delivery attempts: Move it to a different stream, e.g. dead-mystream, Log or alert it
- Limit stream size Auto-trim



Q: why would i use redis stream, i feel it has many difficulies especially on debugging?
    - easy setup
    - fast performance
    - is presistence
    - bad with auto retry (it has to be done manually)
    - bad for dead letters (it has to be done manually)

   | Feature / Need          | Redis Streams | RabbitMQ | Kafka |
|--------------------------|---------------|----------|-------|
| Setup difficulty         | ⭐ Easy        | ⭐⭐ Medium | ⭐⭐⭐ Hard |
| Performance (throughput) | ⭐⭐⭐ Fast      | ⭐⭐ Medium  | ⭐⭐⭐⭐ Massive |
| Persistence              | Optional       | Yes      | Yes (designed for it) |
| Message retention        | Short/limited  | Low/TTL  | Long-term |
| Auto retry               | Manual         | Yes      | Manual/offset-based |
| Dead-letter support      | Manual         | Yes      | Manual |
| Good for background jobs | ✅ Yes         | ✅ Yes    | ❌ Usually no |
| Event streaming          | ⚠️ Limited     | ❌ No     | ✅ Best |
| Replay old messages      | ❌ No          | ❌ No     | ✅ Yes |
| Horizontal consumers     | ✅ Yes         | ✅ Yes    | ✅ Yes |
| Cluster deployment       | ✅ Easy        | ✅ Medium | ⚠️ Complex |
| Workload size            | Small/Medium   | Medium   | Large |
