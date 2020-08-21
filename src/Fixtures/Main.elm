module PAGE.Main exposing (..)

import LaravelElm
import Dict exposing (Dict)
import Html exposing (Html, div, text)
import Html.Attributes exposing (class)
import Json.Decode exposing (Decoder, Error, Value, dict, list, string, succeed)
import Json.Decode.Pipeline exposing (required)


type alias Props =
    { errors : Errors }


type alias State =
    {}


type alias Model =
    { props : Props
    , state : State
    }


type alias Errors =
    Dict String (List String)


type Msg
    = NewProps Value


decodeProps : Decoder Props
decodeProps =
    succeed Props
        |> required "errors" (dict (list string))


stateFromProps : Props -> State
stateFromProps props =
    {}


main : Program Value { props : Result Error Props, state : Maybe State } Msg
main =
    LaravelElm.page
        { decodeProps = decodeProps
        , stateFromProps = stateFromProps
        , update = update
        , view = view
        , subscriptions = \_ -> LaravelElm.receiveNewProps NewProps
        }


update : Msg -> Model -> ( Model, Cmd Msg )
update msg { props, state } =
    case msg of
        NewProps newProps ->
            ( { props =
                    LaravelElm.handleNewProps
                        { decodeProps = decodeProps
                        , previousProps = props
                        , newProps = newProps
                        }
              , state = state
              }
            , Cmd.none
            )


view : Model -> Html Msg
view { props, state } =
    div [ class "container mx-auto m-4 p-4" ]
        [ text "Hello, PAGE." ]
