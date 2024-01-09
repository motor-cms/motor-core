<h1>Artisan commands</h1>

@foreach ($commands as $command)
    <h2>{{$command->getName()}}</h2>
    <p>{{$command->getDescription()}}</p>
    <p>{{$command->getSynopsis()}}</p>
@endforeach
