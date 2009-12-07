@host = host_without_www(request.host)

def host_without_www(host)
  parts = host.split('.')
  if parts[0] == 'www' and parts.count <= 3
    host = parts[1..parts.count-1].join('.')
  end
  return host
end
