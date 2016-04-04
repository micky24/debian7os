#!/usr/bin/python
# gunakan perintah python portfwd [port asal] [port tujuan]
# methode di bawah ini maksudnya kalau ada methode yang kebawa akan di hapus

import socket,asyncore,sys
methode=['CONNECT','HEAD','GET','DELETE','POST','OPTIONS','PUT','TRACE']

class forwarder(asyncore.dispatcher):
    def __init__(self, ip, port, remoteip,remoteport,backlog=5):
        asyncore.dispatcher.__init__(self)
        self.remoteip=remoteip
        self.remoteport=remoteport
        self.create_socket(socket.AF_INET,socket.SOCK_STREAM)
        self.set_reuse_addr()
        self.bind((ip,port))
        self.listen(backlog)

    def handle_accept(self):
        conn, addr = self.accept()
        print 'Connect from ',addr
        sender(receiver(conn),self.remoteip,self.remoteport)

class receiver(asyncore.dispatcher):
    def __init__(self,conn):
        asyncore.dispatcher.__init__(self,conn)
        self.from_remote_buffer=''
        self.to_remote_buffer=''
        self.sender=None

    def handle_connect(self):
        pass

    def handle_read(self):
        read = self.recv(4096)
        for inject in methode:
            if read.find(inject)> -1:
                read = ''
        self.from_remote_buffer += read

    def writable(self):
        return (len(self.to_remote_buffer) > 0)

    def handle_write(self):
        sent = self.send(self.to_remote_buffer)
        self.to_remote_buffer = self.to_remote_buffer[sent:]

    def handle_close(self):
        self.close()
        if self.sender:
            self.sender.close()

class sender(asyncore.dispatcher):
    def __init__(self, receiver, remoteaddr,remoteport):
        asyncore.dispatcher.__init__(self)
        self.receiver=receiver
        receiver.sender=self
        self.create_socket(socket.AF_INET, socket.SOCK_STREAM)
        self.connect((remoteaddr, remoteport))

    def handle_connect(self):
        pass

    def handle_read(self):
        read = self.recv(4096)
        self.receiver.to_remote_buffer += read

    def writable(self):
        return (len(self.receiver.from_remote_buffer) > 0)

    def handle_write(self):
        sent = self.send(self.receiver.from_remote_buffer)
        self.receiver.from_remote_buffer = self.receiver.from_remote_buffer[sent:]

    def handle_close(self):
        self.close()
        self.receiver.close()

if __name__=='__main__':
    from sys import argv
    if argv[1:]:
        forward_from = int(argv[1])
        if argv[2:]:
            forward_to = int(argv[2])
        else:
            forward_from = 443
            forward_to = 22
    else:
        forward_from = 443
        forward_to = 22
        
print "******port forwarding with auto delete payload by mikodemos*****"
print "Listen on port:",forward_from," forward to port:",forward_to    
forwarder('0.0.0.0',forward_from,'127.0.0.1',forward_to)
asyncore.loop()


